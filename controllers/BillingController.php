<?php
use Carbon\Carbon;

class BillingController extends AjaxController {

    const GST_PERCENTAGE = 5;
    const GST_DESCRIPTION = "GST 5%";

    // ================== PLANS ==========================================

    // New de-facto plan as of December 2015
    const STANDARD_ANNUAL_PLAN_SWITCH_VALUE = 0;  // When the user selects a plan from the dropdown list, this value should correspon to the value of the select box
    const STANDARD_ANNUAL_PLAN = 'Standard Plan';
    const STANDARD_ANNUAL_PLAN_PRICE = 2500;      // Used for displaying the price of the plan in the webapp and for calculating GST.
    
    const FULL_SERVICE_PLAN_SWITCH_VALUE = 1;     // When the user selects a plan from the dropdown list, this value should correspon to the value of the select box
    const FULL_SERVICE_PLAN = 'Full Service Plan';
    const FULL_SERVICE_PLAN_PRICE = 7500;         // Used for displaying the price of the plan in the webapp and for calculating GST.

    // ================== /END PLANS =====================================


    // ================== SERVICES =======================================

    // One time services
    const TICKET_UPLOAD_SERVICE = 1;
    const SAFETY_MANUAL_UPLOAD_SERVICE = 2; 
    const SAFETY_MANUAL_AND_TICKET_UPLOAD_SERVICE = 3; 
    const FULL_SERVICE_PLAN_SWITCH = 4;

    // ALL PRICED LISTED IN CENTS
    const SAFETY_MANUAL_AND_TICKET_UPLOAD_SERVICE_PRICE = 54900; // $549
    const SAFETY_MANUAL_UPLOAD_SERVICE_PRICE = 34900; // $349
    const TICKET_UPLOAD_SERVICE_PRICE = 29900; // $299

    // ALL PRICED LISTED IN CENTS
    const SAFETY_MANUAL_AND_TICKET_UPLOAD_SERVICE_DESC = 'Full safety manual and ticket upload service ($549 CAD + GST 5%)';
    const SAFETY_MANUAL_UPLOAD_SERVICE_DESC = 'Safety manual upload service ($349 CAD + GST 5%)';
    const TICKET_UPLOAD_SERVICE_DESC = 'Ticket upload service ($299 CAD + GST 5%)';
    const FULL_SERVICE_PLAN_DESC = 'Full service plan upgrade';

    // ================== /END SERVICES ===================================

    
    // Discontinued Plans
    const TIER_1_PRICING = "Group Edition 1";
    const TIER_2_PRICING = "Group Edition 2";
    const TIER_3_PRICING = "Group Edition 3";
    
    const TIER_1_PRICE = 12;
    const TIER_2_PRICE = 8;
    const TIER_3_PRICE = 6;

    // Number of days given as a free trial on OUR END (not on Stripe's end)
    const FREE_TRIAL_DAYS = 30;
    
    public static function getPricing($pricingTier){
        switch($pricingTier){
            case self::STANDARD_ANNUAL_PLAN:
                return self::STANDARD_ANNUAL_PLAN_PRICE;
            case self::FULL_SERVICE_PLAN:
                return self::FULL_SERVICE_PLAN_PRICE;
            case self::TIER_1_PRICING:
                return self::TIER_1_PRICE;
            case self::TIER_2_PRICING:
                return self::TIER_2_PRICE;
            case self::TIER_3_PRICING:
                return self::TIER_3_PRICE;
            default:
                return 0;
        }
    }
    
    const COUPON_10_FREE = '10orless';
    
    /**
    * Called when a new user signs up
    */
    public function freeTrialAction(){
        $input = Input::all();
        
        $validator = Validator::make($input, 
                array(
                    'company' => 'required|unique:companies,company_name',
                    'email' => 'required|email|unique:admins,email'
                ) 
           );

        if ($validator->fails()){
            return Redirect::to('free-trial')->with(array('error'=>$validator->messages()->first()));
        }

        unset($input['_token']);
        $this->createCompanyAndAdmin($input);
        
        return Redirect::to('login')->with(array('message'=>'Please check your email for your password'));
    }
    
    /**
    * Called when the user submits their credit card details
    * This method contains a lot of try catches, which isn't the prettiest and not implemented in a manner that reflects best practices, but it crudely
    * adds much needed robustness to our calls to the Stripe API.
    */
    public function creditCardDetailsAction(){
        $input = Input::all();

        if(isset($input['coupon_code'])) {
            $couponCode = $input['coupon_code'];
        } else {
            $couponCode = NULL;
        }

        $creditCardToken = $input['stripeToken'];
        $company = Auth::user()->company;

        if ($company->everSubscribed()){
            // Card update will happen before the plan swap to ensure that the card is working and so that the new plan is not given unless they have successfully paid.
            try {
               $this->updateCreditCard($creditCardToken); 
            }
            catch (Exception $e) {
                return Redirect::to('/billing/credit-card-details')->with('error', $e->getMessage());
            }

            // Switch over to yearly plan if they have already subscribed in the past
            if($company->stripe_plan == Self::TIER_1_PRICING){
                try {
                    if($couponCode){
                        Self::swapToStandardPlan($couponCode);
                    } else {
                        Self::swapToStandardPlan();
                    }    
                }
                catch (Exception $e){
                    // Reset it back so they are still restricted
                    $company->stripe_plan = Self::TIER_1_PRICING; 
                    return Redirect::to('/billing/credit-card-details')->with('error', $e->getMessage());
                } 
            }
            
            $updateMessage = 'Billing details changed successfully.';
        } else{
            // Company has never subscribed. The stripe_plan field should therefore be set, but put in A check just in case
            if(isset($input['stripe_plan'])){
                if($input['stripe_plan'] == Self::STANDARD_ANNUAL_PLAN_SWITCH_VALUE){
                    $stripePlanId = Self::STANDARD_ANNUAL_PLAN;
                } elseif ($input['stripe_plan'] == Self::FULL_SERVICE_PLAN_SWITCH_VALUE){
                    $stripePlanId = Self::FULL_SERVICE_PLAN;
                } else {
                    // For some reason the stripe plan value is different.. Probably due to fudging.. So default to standard plan
                    $stripePlanId = Self::STANDARD_ANNUAL_PLAN;
                }
            } else {
                // There was some kind of fudging with the plan code .. Default to standard plan
                $stripePlanId = Self::STANDARD_ANNUAL_PLAN;
            }

            try {
                // But we want to be sure that the user isn't actually charged until their free trial runs out. Same goes for if we extend their free trial.
                $this->subscribe($creditCardToken, $couponCode, $stripePlanId);
                $updateMessage = 'You have successfully subscribed. Thank you.';
            }
            catch (Exception $e) {
                return Redirect::to('/billing/credit-card-details')->with('error', $e->getMessage());
            }
        }

        return Redirect::to('/billing/details')->with('message', $updateMessage);
    }
    
    // Called when a user subscribes for the first time
    protected function subscribe($creditCardToken, $couponCode = NULL, $stripePlanId){
        $company = Auth::user()->company;

        // Create the customer. Include the email of the user who inserted the CC details.
        // Note: subscription() is a StripeGateway object.
        $customer = $company->subscription()->createStripeCustomer($creditCardToken, ['description'=>$company->company_name, 'email'=>Auth::user()->email]);
        
        // Save all the customer data into the COMPANY entity
        $company->subscription()->updateLocalStripeData($customer);

        $trialEndDate = Carbon::createFromTimestamp(strtotime($company->trial_ends_at));

        // Check for coupons and if the user is still on a trial.
        if($couponCode != NULL) {
            if($trialEndDate->isFuture()){
                $company->subscription($stripePlanId)
                        ->trialFor($company->trial_ends_at)
                        ->withCoupon($couponCode)
                        ->create(null, [], $customer);
            } else {
                // Trial is for a very short period so that rather than charging right away, an invoice is created so that we can add tax onto it
                $company->subscription($stripePlanId)
                        ->trialFor(Carbon::now()->addSeconds(1))
                        ->withCoupon($couponCode)
                        ->create(null, [], $customer);
            }
        } else {
            if($trialEndDate->isFuture()){
                $company->subscription($stripePlanId)
                        ->trialFor($company->trial_ends_at)
                        ->create(null, [], $customer);
            } else {
                // Trial is for a very short period so that rather than charging right away, an invoice is created so that we can add tax onto it
                $company->subscription($stripePlanId)
                        ->trialFor(Carbon::now()->addSeconds(1))
                        ->create(null, [], $customer);
                        //->create($creditCardToken, ['description'=>$company->company_name, 'email'=>Auth::user()->email], null);
            }
        }
        
        $company->save();    
    }

    /**
    * Users who swap to the standard plan will have already subscribed in the past. Once all users are switched over, this can actually be removed. 
    */
    protected function swapToStandardPlan($couponCode = NULL){
        $company = Auth::user()->company;
        $customer = $company->subscription()->getStripeCustomer();

        // Remove subscription-specific discount, if any
        if($customer->subscription->discount instanceof Stripe_Object){
            // Remove old coupons that could affect the charge for the new plan
            // This deleteDiscount() method is actually defined in vendor/stripe/stripe-php/lib/Stripe/Subscription.php 
            // Which is NOT Laravel cashier... so it would seem this all works because of a combination betwen Laravel Cashier and that library, somehow.
            $customer->subscription->deleteDiscount();
        }

        // Remove customer discount, if any
        if($customer->discount instanceof Stripe_Object){
            // Remove customer-wide coupons (because in the past we would issue the 10orless coupon on a customer basis -- not a plan basis)
            $customer->deleteDiscount();
        }

        if($couponCode) {
            // Make sure the quantity is switched to 1 as it is only possible to have 1 of the new plan, else user will be massively overcharged
            // Since the user has subscribed in the past, give them 30 trial days before billing them.

            // ->trialFor(Carbon::now()->addSeconds(1))
            $company->subscription(Self::STANDARD_ANNUAL_PLAN)
                    ->trialFor(Carbon::now()->addDays(30))
                    ->withCoupon($couponCode)
                    ->swapAndInvoice(1); 
        } else {
            // Make sure the quantity is switched to 1 as it is only possible to have 1 of the new plan, else user will be massively overcharged
            // Since the user has subscribed in the past, give them 30 trial days before billing them.
            $company->subscription(Self::STANDARD_ANNUAL_PLAN)
                    ->trialFor(Carbon::now()->addDays(30))
                    ->swapAndInvoice(1); 
        }
    }
    
    protected function updateCreditCard($creditCardToken){
        $company = Auth::user()->company;
        // subscription() is a StripeGateway object.
        $company->subscription()->updateCard($creditCardToken);

        // Update the billing email address
        $customer = $company->subscription()->getStripeCustomer();
        $customer->email = Auth::user()->email;
        $customer->save();
    }

    protected function purchaseServiceAction(){
        $company = Auth::user()->company;
        $customer = $company->subscription()->getStripeCustomer();
        $input = Input::all();

        if(!isset($input['service_code']) || $input['service_code'] < 1){
            return self::buildAjaxResponse(FALSE, 'Do not tamper with the form. Please try again.');
        }

        $amount = NULL;
        $packageCode = $input['service_code'];

        if ($packageCode == Self::SAFETY_MANUAL_AND_TICKET_UPLOAD_SERVICE) 
        {
            // Price plus tax
            $amount = Self::SAFETY_MANUAL_AND_TICKET_UPLOAD_SERVICE_PRICE + (Self::SAFETY_MANUAL_AND_TICKET_UPLOAD_SERVICE_PRICE * (Self::GST_PERCENTAGE / 100));
            $description = Self::SAFETY_MANUAL_AND_TICKET_UPLOAD_SERVICE_DESC;
        } 
        elseif ($packageCode == Self::SAFETY_MANUAL_UPLOAD_SERVICE) 
        {
            $amount = Self::SAFETY_MANUAL_UPLOAD_SERVICE_PRICE + (Self::SAFETY_MANUAL_UPLOAD_SERVICE_PRICE * (Self::GST_PERCENTAGE / 100));
            $description = Self::SAFETY_MANUAL_UPLOAD_SERVICE_DESC;
        } 
        elseif ($packageCode == Self::TICKET_UPLOAD_SERVICE) 
        {
            $amount = Self::TICKET_UPLOAD_SERVICE_PRICE + (Self::TICKET_UPLOAD_SERVICE_PRICE * (Self::GST_PERCENTAGE / 100));
            $description = Self::TICKET_UPLOAD_SERVICE_DESC;
        } 
        elseif ($packageCode == Self::FULL_SERVICE_PLAN_SWITCH) {
            $error = null;
            $description = Self::FULL_SERVICE_PLAN_DESC;

            try {
                Self::upgradeToPlan(Self::FULL_SERVICE_PLAN);

                // Human readable description of service for the email to us 
                $user = Auth::user();
                $data = $input;
                $data['service_code'] = $description;
                $data['name'] = $user->first_name . " " . $user->last_name;
                $data['email'] = $user->email;   
                $data['company_name'] = $user->company->company_name;

                Self::sendPurchaseEmail($data);
            }
            catch (Exception $e) {
                $error = $e->getMessage();
            }

            if(!$error){
                return Self::buildAjaxResponse(TRUE, 'You have successfully upgraded your plan!');
            } else {
                return Self::buildAjaxResponse(FALSE, 'There was a problem upgrading your plan: ' + $error);
            }
        }
        else {
            return Self::buildAjaxResponse(FALSE, 'Purchase could not be completed. Try refreshing the page and try again.');
        }

        if($amount){
            try {
                $charge = Stripe_Charge::create(array(
                                          'customer' => $customer->id,
                                          'receipt_email' => Auth::user()->email,
                                          'amount' => $amount,
                                          'currency' => 'cad',
                                          'description' => $description
                                        )); 

                // Human readable description of service for the email to us 
                $user = Auth::user();
                $data = $input;
                $data['service_code'] = $description;
                $data['name'] = $user->first_name . " " . $user->last_name;
                $data['email'] = $user->email;   
                $data['company_name'] = $user->company->company_name;

                Self::sendPurchaseEmail($data);

                return Self::buildAjaxResponse(TRUE);
            }
            catch(Exception $e) {
                return Self::buildAjaxResponse(FALSE, $e->getMessage());
            }
        } else {
            return Self::buildAjaxResponse(FALSE, 'Purchase could not be completed. Try refreshing the page and try again.');
        }    
    }

    protected function sendPurchaseEmail($data) {
        Mail::send('webApp::emails.service-purchased', $data, function($message) use ($data){
            $message->to('sales@whiteknightsafety.com');
            $message->subject('EXCITING: Someone purchased something!');
        });
    }

    protected function upgradeToPlan($stripePlanId){
        $company = Auth::user()->company;
        $customer = $company->subscription()->getStripeCustomer();

        if($customer->subscription->discount instanceof Stripe_Object){
            // Remove old coupons that could affect the charge for the new plan
            // This deleteDiscount() method is actually defined in vendor/stripe/stripe-php/lib/Stripe/Subscription.php 
            // Which is NOT Laravel cashier... so it would seem this all works because of a combination betwen Laravel Cashier and that library, somehow.
            // Note that this WILL maintain the subscription quantity.
            $customer->subscription->deleteDiscount();
        }
        
        $company->subscription($stripePlanId)->swapAndInvoice();
    }

    protected function creditCardDetailsView() {
        $company = Auth::user()->company;
        $data['company'] = $company;
        $plans = [Self::STANDARD_ANNUAL_PLAN, Self::FULL_SERVICE_PLAN];
        $data['plans'] = $plans;
        
        return View::make('webApp::billing.credit-card-details', $data);
    }

    protected function purchaseServicesView() {
        $company = Auth::user()->company;
        $data['company'] = $company;

        return View::make('webApp::billing.purchase-services', $data);
    }

    public function detailsView(){
        $company = Auth::user()->company;
        $data['company'] = $company;
        $data['pricing'] = BillingController::getPricing($company->stripe_plan);
        return View::make('webApp::billing.details',$data);
    }

    public function upgradeToAnnualView(){
        $company = Auth::user()->company;
        $data['company'] = $company;
        return View::make('webApp::billing.upgrade-to-annual', $data);
    }
    
    public function cancelSubscriptionAction($companyId){
        $company = Company::find($companyId);
        
        if (!Request::ajax() || !$company){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        $company->subscription()->cancel();

        return self::buildAjaxResponse(TRUE, $company);
    }
    
    public function resumeSubscriptionAction($companyId){
        $company = Company::find($companyId);

        if (!Request::ajax() || !$company){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        $company->subscription($company->stripe_plan)->resume();

        return self::buildAjaxResponse(TRUE, $company);
    }
    
    public function downloadInvoiceAction($invoiceId){
        $company = Auth::user()->company;
        $logoLink = URL::to('assets/img/wkss/company-logo.png');
        
        return $company->downloadInvoice($invoiceId, [
            'vendor'  => 'WhiteKnightSafety',
            'street' => '734-7th Avenue SW. #540',
            'location' => 'Calgary, AB T2P 3P8',
            'phone' => 'Toll Free: 1.844.944.8356',
            'product' => 'White Knight Software',
            'vat' => 'GST #80526 0171 RT0001',
            'header'=>"<img src='$logoLink' style='width:150px;'/>"
        ]);
    }

    /**
      * Applies a coupon to a subscription. I believe this version of Laravel Cashier only supports one subscription, so a coupon on the customer is a coupon on the susbcription
      * Stripe says: A coupon contains information about a percent-off or amount-off discount you might want to apply to a customer. Coupons only apply to invoices; they do not apply to one-off charges.
      */

    /*
        Important info about invoices:

        Invoices are statements of what a customer owes for a particular billing period, including subscriptions, invoice items, and any automatic proration adjustments if necessary.
        Once an invoice is created, payment is automatically attempted. Note that the payment, while automatic, does not happen exactly at the time of invoice creation. If you have 
        configured webhooks, the invoice will wait until one hour after the last webhook is successfully sent (or the last webhook times out after failing).
        Any customer credit on the account is applied before determining how much is due for that invoice (the amount that will be actually charged). If the amount due for the invoice
         is less than 50 cents (the minimum for a charge), we add the amount to the customer's running account balance to be added to the next invoice. If this amount is negative, 
         it will act as a credit to offset the next invoice. Note that the customer account balance does not include unpaid invoices; it only includes balances that need to be taken 
         into account when calculating the amount due for the next invoice.
    */
    public function applySubscriptionCoupon(){   
        if (!Request::ajax()){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $input = Input::all();
        try {
            Stripe::setApiKey(Config::get("stripe.stripe.secret"));
            Stripe_Coupon::retrieve($input['coupon_code']);

            $company = Auth::user()->company;
            $customer = $company->subscription()->getStripeCustomer();

            // This applies a coupon to the customer as a whole
            //$company->subscription()->applyCoupon($input['coupon_code']);

            // Attempt to apply a coupon directly to a subscription. See this: http://stackoverflow.com/questions/34111995/how-to-add-coupon-to-stripe-subscription-with-laravel-cashier
            //$customer->subscription->withCoupon($input['coupon_code']);

            // In order to apply a coupon to a subscription, their sub must be swapped with a new coupon. This shouldn't change billing and shouldn't charge the user.
            $company->subscription($company->stripe_plan)
                    ->withCoupon($input['coupon_code'])
                    ->swap(); 

            return self::buildAjaxResponse(TRUE);
        } catch(Exception $e) {
            return self::buildAjaxResponse(FALSE, $e->getMessage());
        }
    }

    public function checkCouponValidity($couponId){
        if (!Request::ajax()){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $valid = false;
        $input = Input::all();
        try {
            Stripe::setApiKey(Config::get("stripe.stripe.secret"));
            Stripe_Coupon::retrieve($couponId);
            $valid = true;
        } catch(Exception $e) {
            // $valid is aleady false
        }

        return self::buildAjaxResponse($valid);
    }

    public function removeSubscriptionCoupon(){   
        if (!Request::ajax()){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        try {
            $company = Auth::user()->company;
            $customer = $company->subscription()->getStripeCustomer();

            // Check if the subscription has a discount
            if($customer->subscription->discount instanceof Stripe_Object){
                $customer->subscription->deleteDiscount();
                return self::buildAjaxResponse(TRUE);
            }

            // This is for customer-wide discounts, if that's a route we want to take in the future
            // if(isset($company->subscription()->getStripeCustomer()->discount)) {
            //     //$customer->subscription->deleteDiscount(); // No active discount for subscription sub_7TPgRSEgZfduPz on customer cus_7TPgyPSk6Nhglb
            //     $customer->deleteDiscount();
            //     return self::buildAjaxResponse(TRUE);
            // }

            return self::buildAjaxResponse(FALSE, 'Unable to remove coupon because the subscription does not have a discount.');

        } catch(Exception $e) {
            return self::buildAjaxResponse(FALSE, $e->getMessage());
        }
    }
    
    private function createCompanyAndAdmin($input){
        $company = new Company();
        $company->company_name = $input['company'];
        $company->trial_ends_at = Carbon::now()->addDays(FREE_TRIAL_DAYS);
        $company->save();
        
        $input['role_id'] = UserRole::COMPANY_ADMIN;
        $input['company_id'] = $company->company_id;
        
        $admin = new Admin();
        $password = str_random(4) . rand(1000, 10000) . str_random(4);
        $admin->setFields($input, Hash::make($password));
        $admin->save();
        
        $data = array('email'=>$admin->email,
                      'fullname'=>$admin->first_name.' '.$admin->last_name,
                      'password'=>$password);

        // should be Mail::queue once I get the queue working again
        Mail::send('webApp::emails.registration', $data, function($message) use ($data){
            $message->to($data['email'])->cc('leo@whiteknightsafety.com');
            $message->subject('White Knight Safety Dashboard Login!');
        }); 

        // should be Mail::queue once I get the queue working again
        Mail::send('webApp::emails.free-trial', $data, function($message) use ($data){
            $message->from('sales@whiteknightsafety.com','White Knight Safety Sales Department')
                    ->to($data['email'])->bcc('leo@whiteknightsafety.com');
            $message->subject('White Knight Safety Trial Sign-up!');
        }); 
    }
}