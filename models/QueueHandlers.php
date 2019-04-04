<?php

// /* This was previously used (now used) to calculate the number of workers for billing purposes. We do not calculate costs by users any longer so this class is no longer utilized. 
//    Keeping here as a reference. */
// class WorkerQuantumHandler{
    
//     public function fire($job, $data){

//         if ($job->attempts() > 3){
//             //something is wrong
//             // this should never atually be hit because each job is deleted after it is attempted, regardless
//             $job->delete();
//         }
        
//         $companyId = $data['companyId'];
          
//         $company = Company::find($companyId);

//         // THERE MAY BE A MAJOR BUG IN HERE 
//         // This could fail if the stripe account has been closed 
//         if($company instanceOf Company && $company->everSubscribed() ){

//             $workerCount = Worker::getWorkerCountForCompany($company);
//             $adminCount = Admin::getAdminCountForCompany($company);
//             $gateway = $company->subscription();

//             $customerEntity = $gateway->getStripeCustomer();
//             $totalUserCount = $workerCount+$adminCount;
//             $gateway->updateQuantity($customerEntity, $totalUserCount);

//             $coupon = NULL;
//             if($totalUserCount>249){
//                 $priceTier = BillingController::TIER_3_PRICING;
//             }elseif($totalUserCount>99){
//                 $priceTier = BillingController::TIER_2_PRICING; 
//             }elseif($totalUserCount>10){
//                 $priceTier = BillingController::TIER_1_PRICING;
//             }else{
//                 $priceTier = BillingController::TIER_1_PRICING;
//                 $coupon = BillingController::COUPON_10_FREE;
//             }
//             if (!$company->onPlan($priceTier)){
//                 $company->subscription($priceTier)->swap();
//             }

//             // add or update gst
//             $company->addOrUpdateGST();

//             if(!$coupon && $customerEntity->discount instanceof Stripe_Object){
//                 $customerEntity->deleteDiscount();

//             }
//             if($coupon && !$customerEntity->discount instanceof Stripe_Object){
//                 $gateway->applyCoupon($coupon);
//             }
//         }
//         //Always delete job after processing
//         $job->delete();
//     }
// }