<?php

use Symfony\Component\HttpFoundation\JsonResponse;

class AjaxController extends Controller {
    
    public static function buildAjaxResponse($status, $data = null){
        $content = array();
        
        $content['status'] = $status;
        if (method_exists($data, 'toArray')) {
               $data =  $data->toArray();
        } 
        if ($status){
            $content['data'] = $data;
        }else{
            $content['errors'] = $data;
        }
        
        $response = new JsonResponse;
        $response->setStatusCode(200);
        $response->setContent(json_encode($content));
        
        return $response;
    }
    
    
    public static function validatorFormat($validatorMessages, array $fields){
        $formatted = array();
        foreach ($fields as $f) {
            if ($validatorMessages->has($f)){
                $formatted[$f] = $validatorMessages->get($f);
            }
        }
        return $formatted;
    }
    
    public static function createImageResponse($content, $size = NULL){
        $api = new ApiController();
        return $api->createImageResponse($content);
    }
    
    public static function sendAttachmentEmail($data){
        Mail::send('webApp::emails.export-card', $data, function($message) use ($data){
            $emails = explode(',',$data['email']);
            $validatedEmails = array();
            if (is_array($emails)){
                foreach ($emails as $e){
                    if (filter_var(trim($e),FILTER_VALIDATE_EMAIL)){
                        $validatedEmails[] = trim($e);
                    }
                }
            }
            if (count($validatedEmails)==1){
                $message->to($validatedEmails[0])->subject($data['type']);
            }else{
                $message->to($validatedEmails[0])->subject($data['type']);
                for ($i=1;$i<count($validatedEmails);$i++){
                    $message->cc(trim($validatedEmails[$i]));
                }
            }
            $message->attach($data['attach']);
        });
        unlink($data['attach']);
        return self::buildAjaxResponse(true);
    }
    
    public static function sendAttachmentReportEmail($data){
        Mail::send('webApp::emails.export-daily-signin', $data, function($message) use ($data){
            $emails = explode(',',$data['email']);
            $validatedEmails = array();
            if (is_array($emails)){
                foreach ($emails as $e){
                    if (filter_var(trim($e),FILTER_VALIDATE_EMAIL)){
                        $validatedEmails[] = trim($e);
                    }
                }
            }
            if (count($validatedEmails)==1){
                $message->to($validatedEmails[0])->subject($data['type']);
            }else{
                $message->to($validatedEmails[0])->subject($data['type']);
                for ($i=1;$i<count($validatedEmails);$i++){
                    $message->cc(trim($validatedEmails[$i]));
                }
            }
            $message->attach($data['attach']);
        });
        unlink($data['attach']);
        return self::buildAjaxResponse(true);
    }
    
    public static function sendAttachmentStatsEmail($data){
        Mail::send('webApp::emails.export-company-stats', $data, function($message) use ($data){
            $emails = explode(',',$data['email']);
            $validatedEmails = array();
            if (is_array($emails)){
                foreach ($emails as $e){
                    if (filter_var(trim($e),FILTER_VALIDATE_EMAIL)){
                        $validatedEmails[] = trim($e);
                    }
                }
            }
            if (count($validatedEmails)==1){
                $message->to($validatedEmails[0])->subject($data['type']);
            }else{
                $message->to($validatedEmails[0])->subject($data['type']);
                for ($i=1;$i<count($validatedEmails);$i++){
                    $message->cc(trim($validatedEmails[$i]));
                }
            }
            $message->attach($data['attach']);
        });
        unlink($data['attach']);
        return self::buildAjaxResponse(true);
    }

    // For whole safety manuals
    public static function sendAttachmentSafetyManualEmail($data){
        Mail::send('webApp::emails.export-safety-manual', $data, function($message) use ($data){
            $emails = explode(',',$data['email']);
            $validatedEmails = array();
            if (is_array($emails)){
                foreach ($emails as $e){
                    if (filter_var(trim($e),FILTER_VALIDATE_EMAIL)){
                        $validatedEmails[] = trim($e);
                    }
                }
            }
            if (count($validatedEmails)==1){
                $message->to($validatedEmails[0])->subject($data['type']);
            }else{
                $message->to($validatedEmails[0])->subject($data['type']);
                for ($i=1;$i<count($validatedEmails);$i++){
                    $message->cc(trim($validatedEmails[$i]));
                }
            }
            $message->attach($data['attach']);
        });
        unlink($data['attach']);
        return self::buildAjaxResponse(true);
    }

    // For tickets
    public static function sendAttachmentTicketEmail($data){
        Mail::send('webApp::emails.export-ticket', $data, function($message) use ($data){
            $emails = explode(',',$data['email']);
            $validatedEmails = array();
            if (is_array($emails)){
                foreach ($emails as $e){
                    if (filter_var(trim($e),FILTER_VALIDATE_EMAIL)){
                        $validatedEmails[] = trim($e);
                    }
                }
            }
            if (count($validatedEmails)==1){
                $message->to($validatedEmails[0])->subject($data['type']);
            }else{
                $message->to($validatedEmails[0])->subject($data['type']);
                for ($i=1;$i<count($validatedEmails);$i++){
                    $message->cc(trim($validatedEmails[$i]));
                }
            }
            $message->attach($data['attach']);
        });
        unlink($data['attach']);
        return self::buildAjaxResponse(true);
    }
}