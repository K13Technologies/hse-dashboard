<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;


class ApiController extends Controller {
    
    public $responses;
    
    public static $interval = "ts >=  UNIX_TIMESTAMP() -604800";
    
    /**
     * sets the responses property to the config values
     */
    public function __construct() {
        $this->responses = Config::get('api::responseConstants');
        $this->storageManager = new StorageManager();
    }
    /**
     * Creates a response
     * 
     * @param int $statusCode
     * @param array or object $content
     * @return JsonResponse
     */
    public function createResponse($code, $content = null ){
       
        $response = new JsonResponse;
        $response->setStatusCode($code);
        $response->setContent('');
        if ( $content !== NULL ) {
            
            if (method_exists($content, 'toArray')) {
               $content =  $content->toArray();
            } 
            if ( $code >= 400 ) {
                //create an error object
                $error = new stdClass();
                $error->error = $content;
                //add it as the response content
                $response->setContent(json_encode($error));
            } else {
                $response->setContent(json_encode($content));
            }
        }
        return $response;
    }
    
    
        /**
     * Creates an image response
     * 
     * @param int $statusCode
     * @param file $content
     * @return Response
     */
    public function createImageResponse($content){
        $response = new Response;
        if (!is_file($content)) {
            $response->setStatusCode(404);
            $response->setContent('');
            return Response::create(NULL, 404);
        } else {
            $response->header('Pragma', 'public');
            $response->header('Expires', 0);
            $response->header('Content-Disposition', 'inline');
            $response->header('Cache-Control','private, false');
            $response->header('Content-Type', 'image/jpg');
            $response->header('Content-Transfer-Encoding', 'binary');
            $response->header('Content-Disposition','inline');
            $response->setStatusCode(200);
            $response->sendHeaders();
            $response->setContent(readfile($content));
            return $response;
           
            //LARAVEL STYLE
//            $headers = array(
//                'Content-Disposition' => 'inline',
//                'Cache-Control' => 'must-revalidate',
//                'Expires' => 0,
//                'Pragma' => 'public',
//                'Content-Type' => 'image/jpg',
//            );
//            return $response::create(File::get($content),200,$headers);
        }
        
    }
    
    
    /**
     * This handles the bootstrap call
     * @return JSONResponse Returns an array of UDID's for the company the 
     * authToken's company
     */
    public function bootstrapAction(){
        $response = new stdClass();
        $response->trades = Trade::all()->toArray();
        $response->hazard_activities = HazardActivity::all()->toArray();
        $response->hazard_categories = HazardCategory::all()->toArray();
        $response->positive_observation_activities = PositiveObservationActivity::all()->toArray();
        $response->positive_observation_categories = PositiveObservationCategory::all()->toArray();
        $response->hazard_checklist = HazardChecklistCategory::with('hazardChecklistItems')->get()->toArray();
       
        $worker = Worker::getByAuthToken(Input::get('authToken'));

        $response->flha_list = Flha::getCompletedAndInProgressListsForWorker($worker);
        $response->tailgate_list = Tailgate::getCompletedAndInProgressListsForWorker($worker);
       
        $response->incident_types = IncidentType::all()->toArray();
        
        $response->settings = array('locale' => $worker->locale,
                                    'data_via_wifi' => $worker->data_via_wifi
                                   );
        $schemas = IncidentSchema::getWithParts();
        $response->activities = Activity::all()->toArray();
        
        $response->schemas = $schemas;
        $versionPath = public_path('WhiteKnight.txt');
        $version = File::get($versionPath);
        $response->app_version =  trim($version);
        return $this->createResponse(200, $response);
    }
    
    
    
    public function appVersionAction(){
        $response = new stdClass();
        $versionPath = public_path('WhiteKnight.txt');
        $version = File::get($versionPath);
        $response->app_version =  trim($version);
        return $this->createResponse(200, $response);
    }
    
    
    public function getWorkersForProjectAction(){
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $workers = $worker->group->getAllWorkersForSignin($worker);
        return $this->createResponse(200, $workers);
    }
}