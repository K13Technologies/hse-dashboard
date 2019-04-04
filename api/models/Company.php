<?php
use Laravel\Cashier\BillableTrait;
use Laravel\Cashier\BillableInterface;
/**
* This class implements the BillableInterface as it is require by Laravel Cashier
*/
class Company extends Eloquent implements BillableInterface {

    use BillableTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'companies';
    protected $primaryKey = 'company_id';
    public $timestamps = false;
    protected $dates = ['trial_ends_at', 'subscription_ends_at'];
    
    // If your application offers a free-trial with no credit-card up front, set the cardUpFront property on your model to false. On account creation, be sure to set the trial end date on the model.
    protected $cardUpFront = false;
    
    
    public static function getFormatedForAdd() {
        $companies = array();
        foreach (self::orderBy('company_name')->get()->all() as $c) {
            $companies[$c->company_id] = $c->company_name;
        }
        return $companies;
    }

    public function divisions() {
        return $this->hasMany('Division', 'company_id');
    }

    public function workers() {
        return $this->hasMany('Worker', 'company_id');
    }

    public function companyAdmins() {
        return $this->hasMany('Admin');
    }

    public function vehicles() {
        return $this->hasMany('Vehicle');
    }

    public function photos() {
        return $this->morphMany('Photo', 'imageable');
    }

    public function helplines() {
        return $this->hasMany('Helpline');
    }

    public function radioStations() {
        return $this->helplines()->where('type', '=', Helpline::RADIO_STATION);
    }

    public function phoneNumbers() {
        return $this->helplines()->where('type', '=', Helpline::PHONE_NUMBER);
    }
    
    public static function getByCustomerId($customerId){
        return self::where('stripe_id','=',$customerId)->get()->first();
    }
    
    public function logo() {
        $currentPhotos = $this->photos();
        if (count($currentPhotos))
            return $currentPhotos->first();
        return false;
    }

    public function safetyManual() {
        return $this->hasOne('SafetyManual', 'company_id');
    }

    public static function getTotalCostSavingsForCompany(Admin $admin) {
        $formSavingMultiplier = 20.90;
        $formCount = 0;

        $nearMissCount =    count(NearMiss::getForCompany($admin));
        $hazardCount =      count(Hazard::getForCompany($admin));
        $foCount =          count(PositiveObservation::getForCompany($admin));
        $flhaCount =        count(Flha::getForCompany($admin));
        $tailgateCount =    count(Tailgate::getForCompany($admin));
        $incidentCount =    count(Incident::getForCompany($admin));
        $journeyCount =     count(JourneyV2::getForCompany($admin));
        $inspectionCount =  Vehicle::getTotalInspectionCount($admin);
        
        $formCount = $nearMissCount + $hazardCount + $foCount + $flhaCount + $tailgateCount + $incidentCount + $journeyCount + $inspectionCount;

        return $formCount * $formSavingMultiplier;
    }

    public static function getTotalTreesSavedForCompany(Admin $admin) {
        $sheetsPerTreeDenominator = 8333; // According to some MIT study, this was the average
        $sheetsSavedPerForm = 1; // Currently going with an extremely conservative number of 1 sheet saved per form
        $formCount = 0;

        $nearMissCount =    count(NearMiss::getForCompany($admin));
        $hazardCount =      count(Hazard::getForCompany($admin));
        $foCount =          count(PositiveObservation::getForCompany($admin));
        $flhaCount =        count(Flha::getForCompany($admin));
        $tailgateCount =    count(Tailgate::getForCompany($admin));
        $incidentCount =    count(Incident::getForCompany($admin));
        $journeyCount =     count(JourneyV2::getForCompany($admin));
        $inspectionCount =  Vehicle::getTotalInspectionCount($admin);
        
        $formCount = $nearMissCount + $hazardCount + $foCount + $flhaCount + $tailgateCount + $incidentCount + $journeyCount + $inspectionCount;
        $formCount += 1000; // Adding 1000 pages for the safety manual 

        return (($formCount * $sheetsSavedPerForm) / $sheetsPerTreeDenominator);
    }

    public static function getUserEngagementLevelsForPreviousMonths(Admin $admin, $months) {
        $dataSet = array();
        $previousMonths = array();

        for ($i = 1; $i <= $months; $i++) {
            //First day from each month
            $previousMonths[$i]['month'] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
        }

        $dataSet = $previousMonths;

        for ($i = 1; $i <= $months; $i++) {
            // we have the months, now get the data 
            $nearMissCount =    count(NearMiss::getForCompanyByMonth($admin, $dataSet[$i]['month']));
            $hazardCount =      count(Hazard::getForCompanyByMonth($admin, $dataSet[$i]['month']));
            $foCount =          count(PositiveObservation::getForCompanyByMonth($admin, $dataSet[$i]['month']));
            $flhaCount =        count(Flha::getForCompanyByMonth($admin, $dataSet[$i]['month']));
            $tailgateCount =    count(Tailgate::getForCompanyByMonth($admin, $dataSet[$i]['month']));
            $incidentCount =    count(Incident::getForCompanyByMonth($admin, $dataSet[$i]['month']));
            $journeyCount =     count(JourneyV2::getForCompanyByMonth($admin, $dataSet[$i]['month']));
            $inspectionCount =  count(Inspection::getForCompanyByMonth($admin, $dataSet[$i]['month']));

            $monthlyFormCount = $nearMissCount + $hazardCount + $foCount + $flhaCount + $tailgateCount + $incidentCount + $journeyCount + $inspectionCount;
            $dataSet[$i]['numForms'] = $monthlyFormCount;
        }

        // Puts the data into chronological order
        asort($dataSet);
        return $dataSet;
    }

    // Returns the specified amount of tickets which are below the warning threshold of 14 days. It also flags tickets which are expired
    // by adding an 'expired' boolean attribute to each ticket. 
    // The order that is retured is most expired first
    public static function getTicketExpiryNotificationsForCompany(Admin $admin, $amount) {
        // Begin date acrobatics
        $expiringTickets = array();
        $today = new DateTimeImmutable(WKSSDate::getCurrentUserLocalTimestampStringWithFormat('Y-m-d'));        
        $InfoDate = $today->modify('+30 days'); // Expires in less than 30 days
        $infoDateString = $InfoDate->format('Y-m-d H:i:s');
        // End date acrobatics
        
        // Get all tickets expiring in less than 30 days
        $expiringTickets = Ticket::where('company_id', '=', $admin->company_id)
                         ->where('expiry_date', '<', $infoDateString)
                         ->orderBy('expiry_date', 'ASC')
                         ->take($amount)
                         ->get();

        if(count($expiringTickets)){
            foreach($expiringTickets as $ticket){
                $ticketExpiryDate = $ticket->expiry_date;
                $ticketExpiryDateTime = new DateTime($ticketExpiryDate);

                $expiresInDays = (int) $ticketExpiryDateTime->diff($today)->format("%a");

                if ($ticketExpiryDateTime < $today) { 
                    $ticket['expired'] = true;
                } 
                else {
                    $ticket['expiresInDays'] = $expiresInDays;
                }
            }
        } else {
            $expiringTickets = NULL;
        }

        return $expiringTickets;
    }

    public static function getRecentFormActivityForCompany(Admin $admin) 
    {
        $nearMisses =   NearMiss::getRecentNearMissesForCompany($admin, 5);
        $hazards =      Hazard::getRecentHazardsForCompany($admin, 5);
        $observations = PositiveObservation::getRecentObservationsForCompany($admin, 5);
        $flhas =        Flha::getRecentFlhasForCompany($admin, 5);
        $tailgates =    Tailgate::getRecentTailgatesForCompany($admin, 5);
        $incidents =    Incident::getRecentIncidentsForCompany($admin, 5);
        $journeys =     JourneyV2::getRecentJourneysForCompany($admin, 5);
        $inspections =  Inspection::getRecentInspectionsForCompany($admin, 5);

        $recentActivities = new \Illuminate\Database\Eloquent\Collection; //Create empty collection
        $recentActivities = $recentActivities->merge($nearMisses);
        $recentActivities = $recentActivities->merge($hazards);
        $recentActivities = $recentActivities->merge($observations);
        $recentActivities = $recentActivities->merge($flhas);
        $recentActivities = $recentActivities->merge($incidents);
        $recentActivities = $recentActivities->merge($journeys);
        $recentActivities = $recentActivities->merge($inspections);

        // If there are recent activities
        if($recentActivities->count()) {
            // Sort the collection on the date it was created (most recent first, which is descending)
            $recentActivities->sortByDesc(function($recentItem) {
                // Special case
                if($recentItem->formTypeName() != 'Journey')
                    return $recentItem->ts;

                return $recentItem->ts_created; // This property for Journeys is named differently because the original devs changed it..                
            });

            // Take the 10 most recent items
            $recentActivities = $recentActivities->slice(0,6);

            return $recentActivities;
        }
    }

    /**
    * Whether or not the company is on the full service plan. The intention of this method is to pull this logic out of the view 
    */
    public function hasFullServicePlan() {
        return $this->stripe_plan == BillingController::FULL_SERVICE_PLAN;
    }

    // ======= MARCEL ADDED FROM NEW LARAVEL CASHIER ============
    // This was something that was added in newer versions of Cashier. I was experimenting with this to see if I could get taxes working better... I couldn't.
    // public function getTaxPercent(){
    //     return BillingController::GST_PERCENTAGE;
    // }
    // ======== /END MARCEL ADDED ================================
    
    public function getBillableName(){
        return $this->company_name;
    }
    
    public function trialDaysLeft(){
        $futureTime = strtotime($this->trial_ends_at);
        return ceil(($futureTime-time())/86400); // 86400 = 1 day
//        time()-(strtotime(Auth::user()->company->trial_ends_at))/24/3600
    }
    public function gracePeriodDaysLeft(){
        $futureTime = strtotime($this->subscription_ends_at);
        return ceil(($futureTime-time())/86400); // 86400 = 1 day
//        time()-(strtotime(Auth::user()->company->trial_ends_at))/24/3600
    }
    
    // This is no longer used 
    // public function workerQuantumChanged($job, $data){
    //     $job->delete();
    //     return;
    // }
}
