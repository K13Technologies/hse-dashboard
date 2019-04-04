<?php

use Illuminate\Http\Response;
use BillingController;
use Carbon\Carbon;

/*  
    Note that this EXTENDS the Laravel\Cashier\WebhookController  which already provides handlers for many of the different kinds of webhooks
    including dealing with failed payments and cancelling subscriptions if that's the case. It appears this was added as a way to charge the user GST
    given that Stripe did not natively support plan GST at the time of this implementation. Essentially, if the payment succeeded, also tack on the GST charge.

    From the Laravel documentation: 

    If you have additional Stripe webhook events you would like to handle, simply extend the Webhook controller. Your method names should correspond to Cashier's
    expected convention, specifically, methods should be prefixed with handle and the name of the Stripe webhook you wish to handle. For example, if you wish to 
    handle the invoice.payment_succeeded webhook, you should add a handleInvoicePaymentSucceeded method to the controller.

    More Stripe info from the Stripe documentation:

    Understand that you only need to use webhooks for behind-the-scenes transactions. The results of most Stripe requests—including charge attempts and customer
    creations—are reported synchronously to your code, and don't require webhooks for verification. (For example, with a charge request, the charge will immediately
    succeed and a Charge object returned, or the charge will immediately fail and an exception will be thrown.)

    If your webhook script performs complex logic, or makes network calls, it's possible the script would timeout before Stripe sees its complete execution. 
    For that reason, you may want to have your webhook endpoint immediately acknowledge receipt by returning a 2xx HTTP status code, and then perform the rest of its duties.

    MORE INFO ON LINE ITEMS, WHICH IS WHAT THIS IS:

    Sometimes you want to add a charge or credit to a customer but only actually charge the customer's card at the end of a regular billing cycle. This is useful 
    for combining several charges to minimize per-transaction fees or having Stripe tabulate your usage-based billing totals.
*/

/*
    UPDATE. I don't even think this ever even gets called. I did a search for 'GST was update do' and 'company not found' and nothing came up in test or live...
*/  
class WebhookController extends Laravel\Cashier\WebhookController {

    /**
     * Handle an invoice creation so that tax may be added to it.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleInvoiceCreated($payload) {
        $response = new Response;
        $response->setStatusCode(200);

        /* 
            From the Stripe docs: 
            Whenever an API call results in a new or updated subscription, we will create a closed invoice. 
            The invoice object, and its corresponding invoice.created event, then has closed==true from the start.
            If you subscribe your customer to a trial [WE ALWAYS DO THIS], the initial invoice that we create, pay, and close will be for $0, and closed==true.
            You cannot add invoice items or make any other modifications that affect the charge to the invoice once this has 
            happened (you can, however, update metadata), since your customer has already been charged. 
            Your webhook endpoint should take this into account by not modifying an invoice if closed==true. 
            (Our API will return an error if you try to modify a closed invoice.)
        */

        $invoiceIsClosed = $payload['data']['object']['closed'];
        
        if($invoiceIsClosed == false){
            // Invoice is open to modification
            $customerId = $payload['data']['object']['customer'];
            $invoiceId = $payload['data']['object']['id'];
            $company = Company::getByCustomerId($customerId);
            
            // New users with their first $0 invoice will not have this field set. The invoice will be closed so no changes will be attempted. This just gets rid of the error.
            if($company && $company->subscription()){
                $customerEntity = $company->subscription()->getStripeCustomer();

                // The payload total will always be in cents and represented as an integer, but cast it just in case
                $total = (int) $payload['data']['object']['total'];

                if($total > 1) {
                    try {
                        // Cast it to an int because we can only work with integers as it's the lowest currency denominator (cents)
                        // Also, round up or down to the nearest cent
                        $gstTotal = (int) round( $total * (BillingController::GST_PERCENTAGE / 100) );

                        // "invoice" => The ID of an existing invoice to add this invoice item to. When left blank, the invoice item will be added to the next
                        // upcoming scheduled invoice. Use this when adding invoice items in response to an invoice.created webhook. You cannot add an invoice item to an invoice that has already been paid, attempted or closed.
                        
                        // $invoiceItem amount is in cents, because $total is in cents
                        $invoiceItem = ["amount" => $gstTotal, 
                                        "currency" => "cad",
                                        "description" => BillingController::GST_DESCRIPTION,
                                        "discountable" => false,
                                        "invoice" => $invoiceId];

                        $customerEntity->addInvoiceItem($invoiceItem);
                        
                        $response->setContent('SUCCESSFULLY ADDED TAX TO INVOICE FOR AMOUNT: ' . $gstTotal); 
                     }
                     catch (Exception $e) {
                         $response->setContent($e->getMessage()); 
                     }
                }  else {
                    $response->setContent('TOTAL IS LESS THAN ONE -- NO TAX APPLIED'); 
                }
            } else {
                $response->setContent('No subscription found for user - no tax applied!');  
            }    
        } else {
            $response->setContent('INVOICE CREATION HANDLED WITH NO INVOICE MODIFICATIONS BECAUSE INVOICE IS CLOSED');  
        }
        
        return $response;
    }


    /**
     * Handle a failed payment from a Stripe subscription.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleInvoicePaymentFailed(array $payload)
    {
        $responseMessage = 'Webhook Handled For Failed Payment (Email)';
        $customerId = $payload['data']['object']['customer'];
        $invoiceId = $payload['data']['object']['id'];
        $company = Company::getByCustomerId($customerId);

        if($company && $company->subscription()){
            $customerEntity = $company->subscription()->getStripeCustomer();

            if($payload['data']['object']['attempt_count'] < 3){
                Mail::send('webApp::emails.failed-payment', [], function($message) use ($customerEntity){
                    // $message->to($data['email'])->cc('leo@whiteknightsafety.com');
                    $message->to($customerEntity->email)->bcc('sales@whiteknightsafety.com');
                    $message->subject('Failed Payment for White Knight Safety Invoice');
                });

                $responseMessage = 'Failed payment handled - user emailed';
            } else {
                // Cancel their subscription
                $company->stripe_active = 0;
                $company->subscription_ends_at = Carbon::now();

                // Tell them about it
                Mail::send('webApp::emails.subscription-cancelled', [], function($message) use ($customerEntity){
                    $message->to($customerEntity->email)->bcc('sales@whiteknightsafety.com');
                    $message->subject('White Knight Safety SubScription Cancelled Due to Unpaid Invoice');
                });

                $responseMessage = 'Failed payment handled - user subscription cancelled';
            }
        } else {
            $responseMessage = 'Failed payment handled - company or subscription not found.';
        }

        return new Response($responseMessage, 200);
    }


    // public function handleCustomerSubscriptionDeleted($payload)

    // protected function handleCustomerSubscriptionDeleted(array $payload)
    //     {
    //         $billable = $this->getBillable($payload['data']['object']['customer']);

    //         if ($billable && $billable->subscribed()) {
    //             $billable->subscription()->cancel();
    //         }

    //         // Now set customer on a free plan here

    //         return response('Webhook Handled', 200);
    //     }


    // /**
    //      * Handle the clients subscription being deleted with immediate effect
    //      * type: customer.subscription.deleted
    //      *
    //      * @param array $payload
    //      * @return Response
    //      */
    //     public function handleCustomerSubscriptionDeleted($payload)
    //     {
    //         // Handle The Event
    //         $billable = $this->getBillable($payload['data']['object']['customer']);

    //         /**
    //          * Mark the client subscription as having been cancelled
    //          * Note: We don't call $billable->subscription()->cancel() for 2 reasons:
    //          *     - The subscription has already been cancelled on Stripe
    //          *     - That wouldn't cancel with immediate effect
    //          */
    //         if($billable && $billable->subscribed())
    //         {
    //             $billable->stripe_active = false;
    //             $billable->subscription_ends_at = $payload['data']['object']['ended_at'];
    //             $billable->save();
    //         }

    //         return $this->getWebhookHandledResponse();

    //     }

    //     *
    //      * Handle the clients subscription being updated
    //      * type: customer.subscription.updated
    //      *
    //      * @param array $payload
    //      * @return Response
         
    //     public function handleCustomerSubscriptionUpdated($payload)
    //     {
    //         // Handle The Event
    //         $billable = $this->getBillable($payload['data']['object']['customer']);

    //         if($billable) {

    //             /**
    //              * Determine whether the action was to cancel the subscription at the period end
    //              */
    //             if(array_get($payload, 'data.object.cancel_at_period_end')) {
    //                 $billable->stripe_active = false;
    //                 $billable->subscription_ends_at = Carbon::createFromTimestamp(array_get($payload, 'data.object.current_period_end'));
    //             }

    //             if($billable->isDirty()) {
    //                 $billable->save();
    //             }
    //         }

    //         return $this->getWebhookHandledResponse();

    //     }

    //     protected function getWebhookHandledResponse() {
    //         return Response::make('Webhook Handled');
    //     }
}