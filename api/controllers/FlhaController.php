<?php

class FlhaController extends ApiController {

    public function addFlhaAction() {

        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $input = Request::json()->all();

        $timestamp = WKSSDate::getTsFromDateWithTz($input['created_at']);
        $object = Flha::firstOrNew(array('ts' => $timestamp,
                    'created_at' => $input['created_at'],
                    'worker_id' => $worker->worker_id));
        if ($object->flha_id) {
            return $this->createResponse(201, array('entity_id' => (string) $object->flha_id));
        } else {
            $flha = $object;
            $flha->setFields($input);
            if ($flha->save()) {
                $flha->setLocations($input);
                $flha->setSites($input);
                $flha->setLSDs($input);
                $flha->setPermits($input);
                return $this->createResponse(201, array('entity_id' => (string) $flha->flha_id));
            }
            return $this->createResponse(400);
        }
    }

    public function getOneFlhaAction($flhaId) {
        return Flha::getWithFullDetails($flhaId);
    }

    public function editFlhaAction($flhaId) {
        $input = Request::json()->all();

        $flha = Flha::find($flhaId);
        $flha->setFields($input);

        if ($flha->save()) {
            $flha->setLocations($input);
            $flha->setSites($input);
            $flha->setLSDs($input);
            $flha->setPermits($input);
            return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }

    public function setFlhaChecklistAction($flhaId) {

        if (!Request::json()->has('hazardChecklistItemIds')) {
            return $this->createResponse(400, $this->responses['missingFlhaHazardItems']);
        }
        if (!is_array(Request::json()->get('hazardChecklistItemIds'))) {
            return $this->createResponse(400, $this->responses['invalidFlhaHazardItemsType']);
        }
        $input = Request::json()->all();

        $flha = Flha::find($flhaId);

        if ($flha->setChecklist($input)) {
            return $this->createResponse(200);
        }
        return $this->createResponse(400, $this->responses['invalidFlhaHazardItems']);
    }

    public function setFlhaJobCompletionAction($flhaId) {

        $input = Request::json()->all();

        $flha = Flha::find($flhaId);

        if (!$flha->completion instanceof JobCompletion) {
            $completion = new JobCompletion();
            $completion->setFields($input);
            $timestamp = WKSSDate::getTsFromDateWithTz($input['created_at']);
            $completion->ts = $timestamp;
            $flha->completion()->save($completion);
            return $this->createResponse(200);
        }
        return $this->createResponse(403);
    }

    public function addFlhaTaskAction($flhaId) {
        $input = Request::json()->all();

        $flha = Flha::find($flhaId);

        if (array_key_exists('title', $input)) {
            $object = FlhaTask::firstOrNew(array('title' => $input['title'],
                        'flha_id' => $flha->flha_id));
            if ($object->flha_task_id) {
                return $this->createResponse(201, array('entity_id' => (string) $object->flha_task_id));
            }
        }

        $flhaTask = new FlhaTask();

        $flhaTask->setFields($input);

        if ($flha->tasks()->save($flhaTask)) {
            return $this->createResponse(201, array('entity_id' => (string) $flhaTask->flha_task_id));
        }
        return $this->createResponse(400);
    }

    public function deleteFlhaTaskAction($flhaTaskId) {
        $flhaTask = FlhaTask::find($flhaTaskId);

        if ($flhaTask->delete()) {
            return $this->createResponse(204);
        } else {
            return $this->createResponse(304);
        }
    }

    public function getFlhaTaskAction($flhaTaskId) {
        $flhaTask = FlhaTask::getWithHazardListById($flhaTaskId);

        return $this->createResponse(200, $flhaTask);
    }

    public function addFlhaTaskHazardAction($flhaTaskId) {
        $input = Request::json()->all();

        $flhaTask = FlhaTask::find($flhaTaskId);

        if (array_key_exists('description', $input)) {
            $object = FlhaTaskHazard::firstOrNew(array('description' => $input['description'],
                        'flha_task_id' => $flhaTask->flha_task_id));
            if ($object->flha_task_hazard_id) {
                return $this->createResponse(201, array('entity_id' => (string) $object->flha_task_hazard_id));
            }
        }

        $flhaTaskHazard = new FlhaTaskHazard();
        $flhaTaskHazard->setFields($input);

        if ($flhaTask->hazards()->save($flhaTaskHazard)) {
            return $this->createResponse(201, array('entity_id' => (string) $flhaTaskHazard->flha_task_hazard_id));
        }
        return $this->createResponse(400);
    }

    public function getFlhaTaskHazardAction($flhaTaskHazardId) {

        $flhaTaskHazard = FlhaTaskHazard::find($flhaTaskHazardId);
        return $this->createResponse(200, $flhaTaskHazard);
    }

    public function editFlhaTaskHazardAction($flhaTaskHazardId) {

        $input = Request::json()->all();

        $flhaTaskHazard = FlhaTaskHazard::find($flhaTaskHazardId);
        $flhaTaskHazard->setFields($input);

        if ($flhaTaskHazard->save()) {
            return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }

    public function deleteFlhaTaskHazardAction($flhaTaskHazardId) {
        $flhaTaskHazard = FlhaTaskHazard::find($flhaTaskHazardId);

        if ($flhaTaskHazard->delete()) {
            return $this->createResponse(204);
        } else {
            return $this->createResponse(304);
        }
    }

    public function addFlhaSpotcheckAction($flhaId) {
        $input = Request::json()->all();

        $flha = Flha::find($flhaId);
        $timestamp = WKSSDate::getTsFromDateWithTz($input['created_at']);
        $object = Spotcheck::firstOrNew(array('ts' => $timestamp,
                    'created_at' => $input['created_at'],
                    'first_name' => $input['first_name'],
                    'last_name' => $input['last_name'],
                    'flha_id' => $flha->flha_id));
        if ($object->spotcheck_id) {
            return $this->createResponse(201, array('entity_id' => (string) $object->spotcheck_id));
        } else {
            $spotcheck = $object;
            $spotcheck->setFields($input);

            if ($flha->spotchecks()->save($spotcheck)) {
                return $this->createResponse(201, array('entity_id' => (string) $spotcheck->spotcheck_id));
            }
            return $this->createResponse(400);
        }
    }

    public function getFlhaSpotcheckAction($flhaSpotcheckId) {

        $spotcheck = Spotcheck::find($flhaSpotcheckId);
        return $this->createResponse(200, $spotcheck);
    }

    public function editFlhaSpotcheckAction($flhaSpotcheckId) {

        $input = Request::json()->all();

        $spotcheck = Spotcheck::find($flhaSpotcheckId);
        $spotcheck->setFields($input);

        if ($spotcheck->save()) {
            return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }

    public function addVisitorForSignoffAction($flhaId) {
        $input = Request::json()->all();

        $flha = Flha::find($flhaId);

        $object = SignoffVisitor::firstOrNew(array('first_name' => $input['first_name'],
                    'last_name' => $input['last_name'],
                    'flha_id' => $flha->flha_id));
        if ($object->signoff_visitor_id) {
            return $this->createResponse(201, array('entity_id' => (string) $object->signoff_visitor_id));
        }

        $visitor = new SignoffVisitor();
        $visitor->setFields($input);
        if ($flha->signoffVisitors()->save($visitor)) {
            return $this->createResponse(201, array('entity_id' => (string) $visitor->signoff_visitor_id));
        }
        return $this->createResponse(400);
    }

    public function getVisitorForSignoffAction($visitorId) {

        $signoffVisitor = SignoffVisitor::find($visitorId);
        return $this->createResponse(200, $signoffVisitor);
    }

    public function editVisitorForSignoffAction($visitorId) {

        $input = Request::json()->all();

        $signoffVisitor = SignoffVisitor::find($visitorId);
        $signoffVisitor->setFields($input);

        if ($signoffVisitor->save()) {
            return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }

    public function deleteVisitorForSignoffAction($visitorId) {
        $signoffVisitor = SignoffVisitor::find($visitorId);

        if ($signoffVisitor->delete()) {
            return $this->createResponse(204);
        } else {
            return $this->createResponse(304);
        }
    }

    public function addWorkerForSignoffAction($flhaId) {
        $input = Request::json()->all();

        $flha = Flha::find($flhaId);

        $object = SignoffWorker::firstOrNew(array('first_name' => $input['first_name'],
                    'last_name' => $input['last_name'],
                    'flha_id' => $flha->flha_id));
        if ($object->signoff_worker_id) {
            return $this->createResponse(201, array('entity_id' => (string) $object->signoff_worker_id));
        }

        $signoffWorker = new SignoffWorker();
        $signoffWorker->setFields($input);
        if ($flha->signoffWorkers()->save($signoffWorker)) {
            return $this->createResponse(201, array('entity_id' => (string) $signoffWorker->signoff_worker_id));
        }
        return $this->createResponse(400);
    }

    public function getWorkerForSignoffAction($workerId) {

        $signoffWorker = SignoffWorker::find($workerId);
        $signoffWorker->load('breaks');
        return $this->createResponse(200, $signoffWorker);
    }

    public function editWorkerForSignoffAction($workerId) {

        $input = Request::json()->all();

        $signoffWorker = SignoffWorker::find($workerId);
        $signoffWorker->setFields($input);

        if ($signoffWorker->save()) {
            return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }

    public function deleteWorkerForSignoffAction($workerId) {
        $signoffWorker = SignoffWorker::find($workerId);

        if ($signoffWorker->delete()) {
            return $this->createResponse(204);
        } else {
            return $this->createResponse(304);
        }
    }

    public function addBreakForSignoffAction($workerId) {
        $input = Request::json()->all();

        $signoffWorker = SignoffWorker::find($workerId);

        $timestamp = WKSSDate::getTsFromDateWithTz($input['created_at']);
        $object = SignoffBreak::firstOrNew(array('ts' => $timestamp,
                    'created_at' => $input['created_at'],
                    'signoff_worker_id' => $signoffWorker->signoff_worker_id));
        if ($object->signoff_break_id) {
            return $this->createResponse(201, array('entity_id' => (string) $object->signoff_break_id));
        } else {
            $signoffBreak = $object;
            $signoffBreak->setFields($input);
            if ($signoffWorker->breaks()->save($signoffBreak)) {
                return $this->createResponse(201, array('entity_id' => (string) $signoffBreak->signoff_break_id));
            }
            return $this->createResponse(400);
        }
    }

    public function deleteBreakForSignoffAction($breakId) {
        $signoffBreak = SignoffBreak::find($breakId);

        if ($signoffBreak->delete()) {
            return $this->createResponse(204);
        } else {
            return $this->createResponse(304);
        }
    }

    public function saveFlhaPhotoAction($flhaId) {
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $flha = Flha::find($flhaId);
        if (Input::hasFile('photo')) {
            $flhaPhoto = Input::file('photo');

            $photo = Photo::firstOrNew(array('original_name' => $flhaPhoto->getClientOriginalName(),
                        'imageable_type' => 'Flha',
                        'imageable_id' => $flha->flha_id,
                        'worker_id' => $worker->worker_id));

            if ($photo->photo_id) {
                return $this->createResponse(201, array('entity_id' => (string) $photo->name));
            } else {
                $flhaPhotoDetails = $this->storageManager->saveFlhaPhoto($flha, $flhaPhoto);

                $photo = new Photo();
                $photo->path = $flhaPhotoDetails['path'];
                $photo->name = $flhaPhotoDetails['name'];
                $photo->original_name = $flhaPhoto->getClientOriginalName();
                $photo->worker_id = $worker->worker_id;
                $flha->photos()->save($photo);

                return $this->createResponse(201, array('entity_id' => $flhaPhotoDetails['name']));
            }
        }
        return $this->createResponse(400);
    }

    public function getFlhaPhotoAction($photoId) {
        $photo = Photo::getPhotoByPhotoName($photoId);
        return $this->createImageResponse($photo->path);
    }

    public function deleteFlhaPhotoAction($photoId) {
        $photo = Photo::getPhotoByPhotoName($photoId);

        if ($photo->delete()) {
            $this->storageManager->deletePhoto($photo);
            return $this->createResponse(204);
        }
        return $this->createResponse(304);
    }

    public function saveFlhaSpotcheckSignatureAction($flhaSpotcheckId) {
        $spotcheck = Spotcheck::find($flhaSpotcheckId);
        if (Input::hasFile('photo')) {
            $signaturePhoto = Input::file('photo');
            $this->storageManager->saveSpotcheckSignaturePhoto($spotcheck, $signaturePhoto);
            return $this->createResponse(201);
        }
        return $this->createResponse(400);
    }

    public function getFlhaSpotcheckSignatureAction($flhaSpotcheckId) {
        $spotcheck = Spotcheck::find($flhaSpotcheckId);
        $photo = $this->storageManager->getSpotcheckSignaturePhoto($spotcheck);
        return $this->createImageResponse($photo);
    }

    public function saveVisitorSignoffPhotoAction($visitorId) {
        $visitor = SignoffVisitor::find($visitorId);
        if (Input::hasFile('photo')) {
            $photo = Input::file('photo');
            $this->storageManager->saveSignoffVisitorPhoto($visitor, $photo);
            return $this->createResponse(201);
        }
        return $this->createResponse(400);
    }

    public function getVisitorSignoffPhotoAction($visitorId) {
        $visitor = SignoffVisitor::find($visitorId);
        $photo = $this->storageManager->getSignoffVisitorPhoto($visitor);
        return $this->createImageResponse($photo);
    }

    public function saveVisitorSignoffSignatureAction($visitorId) {
        $visitor = SignoffVisitor::find($visitorId);
        if (Input::hasFile('photo')) {
            $photo = Input::file('photo');
            $this->storageManager->saveSignoffVisitorSignature($visitor, $photo);
            return $this->createResponse(201);
        }
        return $this->createResponse(400);
    }

    public function getVisitorSignoffSignatureAction($visitorId) {
        $visitor = SignoffVisitor::find($visitorId);
        $photo = $this->storageManager->getSignoffVisitorSignature($visitor);
        return $this->createImageResponse($photo);
    }

    public function saveWorkerSignoffPhotoAction($workerId) {
        $worker = SignoffWorker::find($workerId);
        if (Input::hasFile('photo')) {
            $photo = Input::file('photo');
            $this->storageManager->saveSignoffWorkerPhoto($worker, $photo);
            return $this->createResponse(201);
        }
        return $this->createResponse(400);
    }

    public function getWorkerSignoffPhotoAction($workerId) {
        $worker = SignoffWorker::find($workerId);
        $photo = $this->storageManager->getSignoffWorkerPhoto($worker);
        return $this->createImageResponse($photo);
    }

    public function saveWorkerSignoffSignatureAction($workerId) {
        $worker = SignoffWorker::find($workerId);
        if (Input::hasFile('photo')) {
            $photo = Input::file('photo');
            $this->storageManager->saveSignoffWorkerSignature($worker, $photo);
            return $this->createResponse(201);
        }
        return $this->createResponse(400);
    }

    public function getWorkerSignoffSignatureAction($workerId) {
        $worker = SignoffWorker::find($workerId);
        $photo = $this->storageManager->getSignoffWorkerSignature($worker);
        return $this->createImageResponse($photo);
    }

}
