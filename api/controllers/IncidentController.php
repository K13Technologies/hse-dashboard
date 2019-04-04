<?php

class IncidentController extends ApiController {

    public function addIncidentAction() {

        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $input = Request::json()->all();

        $timestamp = WKSSDate::getTsFromDateWithTz($input['created_at']);
        $object = Incident::firstOrNew(array('ts' => $timestamp,
                    'created_at' => $input['created_at'],
                    'worker_id' => $worker->worker_id));
        if ($object->incident_id) {
            return $this->createResponse(201, array('entity_id' => (string) $object->incident_id));
        } else {
            $incident = $object;
            $incident->setFields($input);
            if ($incident->save()) {
                $incident->setTypes($input);
                $incident->setActivities($input);
                return $this->createResponse(201, array('entity_id' => (string) $incident->incident_id));
            }
            return $this->createResponse(400);
        }
    }

    public function getIncidentListAction() {

        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $incidentsList = Incident::getRecentIncidentsForWorker($worker->worker_id);
        return $this->createResponse(200, $incidentsList);
    }

    public function getIncidentAction($incidentId) {
        $incident = Incident::getWithFullDetails($incidentId);
        return $this->createResponse(200, $incident);
    }

    public function editIncidentAction($incidentId) {
        $input = Request::json()->all();
        $incident = Incident::find($incidentId);
        $incident->setFields($input);
        if ($incident->save()) {
            $incident->setTypes($input);
            $incidentTypes = $input['incidentTypeIds'];
            foreach ($incident->mvds as $mvd){
                if (!in_array($mvd->incident_type_id,$incidentTypes)) {
                    $this->deleteIncidentMVDAction($mvd->incident_mvd_id);
                }
            }
            foreach ($incident->treatments as $treatment){
                if (!in_array($treatment->incident_type_id,$incidentTypes)) {
                    $this->deleteIncidentTreatmentAction($treatment->incident_treatment_id);
                }
            }
            if (!$incident->shouldHaveReleaseSpill() && $incident->hasReleaseSpill()) {
                $this->deleteIncidentReleaseSpillAction($incident->incident_id);
            }
            $incident->setActivities($input);
            return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }

    public function addIncidentPersonAction($incidentId) {
        $input = Request::json()->all();
        $incident = Incident::find($incidentId);
        $status = IncidentPerson::savePerson($incident, $input);
        if ($status) {
            return $this->createResponse(201, array('entity_id' => (string) $status));
        } else {
            return $this->createResponse(400);
        }
    }

    public function editIncidentPersonAction($incidentPersonId) {
        $input = Request::json()->all();
        $incidentPerson = IncidentPerson::find($incidentPersonId);
        $incidentPerson->setFields($input);
        if ($incidentPerson->save()) {
            return $this->createResponse(200);
        } else {
            return $this->createResponse(304);
        }
    }

    public function deleteIncidentPersonAction($incidentPersonId) {
        $incidentPerson = IncidentPerson::find($incidentPersonId);
        if ($incidentPerson->delete()) {
            return $this->createResponse(204);
        } else {
            return $this->createResponse(304);
        }
    }

    public function addIncidentMVDAction($incidentId) {
        $input = Request::json()->all();
        $incident = Incident::find($incidentId);
        $incidentTypeId = $input['incident_type_id'];
        if (in_array($incidentTypeId, IncidentType::$mvd)) {
            $incidentType = IncidentType::find($incidentTypeId);
            if (!$incident->hasType($incidentType)) {
                $incident->addType($incidentType);
            }
            $status = IncidentMVD::saveMVD($incident, $input);
            if ($status) {
                return $this->createResponse(201, array('entity_id' => (string) $status));
            } else {
                return $this->createResponse(400);
            }
        } else {
            return $this->createResponse(403);
        }
    }

    public function getIncidentMVDAction($incidentMVDtId) {
        $incidentMVD = IncidentMVD::find($incidentMVDtId)->getWithDetails();
        return $this->createResponse(200, $incidentMVD);
    }

    public function editIncidentMVDAction($incidentMVDId) {
        $input = Request::json()->all();
        $incidentMVD = IncidentMVD::find($incidentMVDId);
        if ($input['vehicleType'] != $incidentMVD->vehicleType) {
            foreach ($incidentMVD->statements as $s) {
                $this->deleteIncidentPartStatementAction($s->part_statement_id);
            }
        }
        $incidentMVD->setFields($input);
        if ($incidentMVD->save()) {
            return $this->createResponse(200);
        } else {
            return $this->createResponse(304);
        }
    }

    public function deleteIncidentMVDAction($incidentMvdId) {
        $incidentMVD = IncidentMVD::find($incidentMvdId);
        $incidentTypeId = $incidentMVD->incident_type_id;
        $incidentMVD->incident->removeType($incidentTypeId);
        foreach ($incidentMVD->statements as $s) {
            $this->deleteIncidentPartStatementAction($s->part_statement_id);
        }
        foreach ($incidentMVD->photos as $photo) {
            $this->deleteIncidentMVDPhotoAction($photo->name);
        }
        if ($incidentMVD->delete()) {
            return $this->createResponse(204);
        } else {
            return $this->createResponse(304);
        }
    }

    public function addIncidentTreatmentAction($incidentId) {
        $input = Request::json()->all();
        $incident = Incident::find($incidentId);
        $incidentTypeId = $input['incident_type_id'];
        if (in_array($incidentTypeId, IncidentType::$medical)) {
            $incidentType = IncidentType::find($incidentTypeId);
            if (!$incident->hasType($incidentType)) {
                $incident->addType($incidentType);
            }
            $status = IncidentTreatment::saveTreatment($incident, $input);
            if ($status) {
                return $this->createResponse(201, array('entity_id' => (string) $status));
            } else {
                return $this->createResponse(400);
            }
        } else {
            return $this->createResponse(403);
        }
    }

    public function getIncidentTreatmentAction($incidentTreatmentId) {
        $incidentTreatment = IncidentTreatment::find($incidentTreatmentId)->getWithDetails();
        return $this->createResponse(200, $incidentTreatment);
    }

    public function editIncidentTreatmentAction($incidentTreatmentId) {
        $input = Request::json()->all();
        $incidentTreatment = IncidentTreatment::find($incidentTreatmentId);
        $incidentTreatment->setFields($input);
        if ($incidentTreatment->save()) {
            return $this->createResponse(200);
        } else {
            return $this->createResponse(304);
        }
    }

    public function deleteIncidentTreatmentAction($incidentTreatmentId) {
        $incidentTreatment = IncidentTreatment::find($incidentTreatmentId);
        $incidentTypeId = $incidentTreatment->incident_type_id;
        $incidentTreatment->incident->removeType($incidentTypeId);
        foreach ($incidentTreatment->statements as $s) {
            $this->deleteIncidentPartStatementAction($s->part_statement_id);
        }
        if ($incidentTreatment->delete()) {
            return $this->createResponse(204);
        } else {
            return $this->createResponse(304);
        }
    }

    public function addIncidentReleaseSpillAction($incidentId) {
        $input = Request::json()->all();
        $incident = Incident::find($incidentId);
        $incidentTypeId = $input['incident_type_id'];
        if (in_array($incidentTypeId, IncidentType::$release_and_spill)) {
            $incidentType = IncidentType::find($incidentTypeId);
            if (!$incident->hasType($incidentType)) {
                $incident->addType($incidentType);
            }
            $status = IncidentReleaseSpill::saveReleaseSpill($incident, $input);
            if ($status) {
                return $this->createResponse(201, array('entity_id' => (string) $status));
            } else {
                return $this->createResponse(400);
            }
        } else {
            return $this->createResponse(403);
        }
    }

    public function getIncidentReleaseSpillAction($incidentId) {
        $incident = Incident::find($incidentId);
        $shouldHaveReleaseSpill = $incident->shouldHaveReleaseSpill();
        $hasReleaseSpill = $incident->hasReleaseSpill();
        if ($shouldHaveReleaseSpill) {
            if ($hasReleaseSpill) {
                $rs = $incident->releaseSpill;
                return $this->createResponse(200, $rs);
            } else {
                return $this->createResponse(204);
            }
        } else {
            return $this->createResponse(403);
        }
    }

    public function editIncidentReleaseSpillAction($incidentId) {
        $input = Request::json()->all();
        $incident = Incident::find($incidentId);
        $hasReleaseSpill = $incident->hasReleaseSpill();
        if ($hasReleaseSpill) {
            $rs = $incident->releaseSpill;
            $rs->setFields($input);
            $rs->save();
            return $this->createResponse(200);
        } else {
            return $this->createResponse(304);
        }
    }

    public function deleteIncidentReleaseSpillAction($incidentId) {
        $incident = Incident::find($incidentId);
        $incident->removeType(IncidentType::$release_and_spill[0]);
        $hasReleaseSpill = $incident->hasReleaseSpill();
        if ($hasReleaseSpill) {
            $rs = $incident->releaseSpill;
            $rs->delete();
            return $this->createResponse(204);
        } else {
            return $this->createResponse(304);
        }
    }

    public function addIncidentMVDPartStatementAction($incidentMVDId) {
        $input = Request::json()->all();
        $incidentMVD = IncidentMVD::find($incidentMVDId);
        $incidentPartId = $input['incident_schema_part_id'];
        $incidentSchemaPart = IncidentSchemaPart::find($incidentPartId);

        $incidentSchemaType = $incidentSchemaPart->schema->type;
        if ($incidentSchemaType == IncidentSchema::TYPE_TRUCK OR
                $incidentSchemaType == IncidentSchema::TYPE_TRAILER) {

            $stmt = IncidentPartStatement::firstOrNew(array('incident_id' => $incidentMVD->incident_id,
                        'incident_schema_part_id' => $incidentPartId,
                        'statementable_id' => $incidentMVDId,
                        'statementable_type' => 'IncidentMVD'));
            $stmt->comment = $input['comment'];
            $stmt->save();
            return $this->createResponse(201, array('entity_id' => (string) $stmt->part_statement_id));
        } else {
            return $this->createResponse(403);
        }
    }
    
    public function addIncidentTreatmentPartStatementAction($incidentTreatmentId) {
        $input = Request::json()->all();
        $incidentTreatment = IncidentTreatment::find($incidentTreatmentId);
        $incidentPartId = $input['incident_schema_part_id'];
        $incidentSchemaPart = IncidentSchemaPart::find($incidentPartId);

        $incidentSchemaType = $incidentSchemaPart->schema->type;
        if ($incidentSchemaType == IncidentSchema::TYPE_BODY) {

            $stmt = IncidentPartStatement::firstOrNew(array('incident_id' => $incidentTreatment->incident_id,
                        'incident_schema_part_id' => $incidentPartId,
                        'statementable_id' => $incidentTreatmentId,
                        'statementable_type' => 'IncidentTreatment'));
            $stmt->comment = $input['comment'];
            $stmt->save();
            return $this->createResponse(201, array('entity_id' => (string) $stmt->part_statement_id));
        } else {
            return $this->createResponse(403);
        }
    }

    public function editIncidentPartStatementAction($incidentStatementId) {
        $input = Request::json()->all();
        $stmt = IncidentPartStatement::find($incidentStatementId);
        $stmt->comment = $input['comment'];
        $stmt->save();
        if ($stmt->save()) {
            return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }

    public function deleteIncidentPartStatementAction($incidentStatementId) {
        $stmt = IncidentPartStatement::find($incidentStatementId);
        foreach ($stmt->photos as $photo) {
            $this->deleteIncidentPartStatementPhotoAction($photo->name);
        }
        if ($stmt->delete()) {
            return $this->createResponse(204);
        }
        return $this->createResponse(304);
    }

    public function saveIncidentPartStatementPhotoAction($incidentStatementId) {
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $stmt = IncidentPartStatement::find($incidentStatementId);
        if (Input::hasFile('photo')) {
            $stmtPhoto = Input::file('photo');
            $photo = Photo::firstOrNew(array('original_name' => $stmtPhoto->getClientOriginalName(),
                        'imageable_type' => 'IncidentPartStatement',
                        'imageable_id' => $stmt->part_statement_id,
                        'worker_id' => $worker->worker_id));
            if ($photo->photo_id) {
                return $this->createResponse(201, array('entity_id' => (string) $photo->name));
            } else {
                $stmtPhotoDetails = $this->storageManager->saveIncidentStatementPhoto($stmt, $stmtPhoto);
                $photo = new Photo();
                $photo->path = $stmtPhotoDetails['path'];
                $photo->name = $stmtPhotoDetails['name'];
                $photo->original_name = $stmtPhoto->getClientOriginalName();
                $photo->worker_id = $worker->worker_id;
                $stmt->photos()->save($photo);
                return $this->createResponse(201, array('entity_id' => $stmtPhotoDetails['name']));
            }
        }
        return $this->createResponse(400);
    }

    public function getIncidentPartStatementPhotoAction($photoId) {
        $photo = Photo::getPhotoByPhotoName($photoId);
        return $this->createImageResponse($photo->path);
    }

    public function deleteIncidentPartStatementPhotoAction($photoId) {
        $photo = Photo::getPhotoByPhotoName($photoId);

        if ($photo->delete()) {
            $this->storageManager->deletePhoto($photo);
            return $this->createResponse(204);
        }
        return $this->createResponse(304);
    }

    public function saveIncidentPhotoAction($incidentId) {
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $incident = Incident::find($incidentId);

        if (Input::hasFile('photo')) {
            $incidentPhoto = Input::file('photo');

            $photo = Photo::firstOrNew(array('original_name' => $incidentPhoto->getClientOriginalName(),
                        'imageable_type' => 'Incident',
                        'imageable_id' => $incident->incident_id,
                        'worker_id' => $worker->worker_id));

            if ($photo->photo_id) {
                return $this->createResponse(201, array('entity_id' => (string) $photo->name));
            } else {
                $hazardPhotoDetails = $this->storageManager->saveIncidentPhoto($incident, $incidentPhoto);

                $photo = new Photo();
                $photo->path = $hazardPhotoDetails['path'];
                $photo->name = $hazardPhotoDetails['name'];
                $photo->original_name = $incidentPhoto->getClientOriginalName();
                $photo->worker_id = $worker->worker_id;
                $incident->photos()->save($photo);

                return $this->createResponse(201, array('entity_id' => $hazardPhotoDetails['name']));
            }
        }
        return $this->createResponse(400);
    }

    public function getIncidentPhotoAction($incidentPhotoId) {
        $photo = Photo::getPhotoByPhotoName($incidentPhotoId);
        return $this->createImageResponse($photo->path);
    }

    public function deleteIncidentPhotoAction($incidentPhotoId) {
        $photo = Photo::getPhotoByPhotoName($incidentPhotoId);

        if ($photo->delete()) {
            $this->storageManager->deletePhoto($photo);
            return $this->createResponse(204);
        }
        return $this->createResponse(304);
    }

    public function addIncidentMVDPhotoAction($incidentMVDId) {
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $incidentMVD = IncidentMVD::find($incidentMVDId);

        if (Input::hasFile('photo')) {
            $incidentMVDPhoto = Input::file('photo');

            $photo = Photo::firstOrNew(array('original_name' => $incidentMVDPhoto->getClientOriginalName(),
                        'imageable_type' => 'IncidentMVD',
                        'imageable_id' => $incidentMVDId,
                        'worker_id' => $worker->worker_id));

            if ($photo->photo_id) {
                return $this->createResponse(201, array('entity_id' => (string) $photo->name));
            } else {
                $photoDetails = $this->storageManager->saveIncidentMVDPhoto($incidentMVD, $incidentMVDPhoto);

                $photo = new Photo();
                $photo->path = $photoDetails['path'];
                $photo->name = $photoDetails['name'];
                $photo->original_name = $incidentMVDPhoto->getClientOriginalName();
                $photo->worker_id = $worker->worker_id;
                $incidentMVD->photos()->save($photo);

                return $this->createResponse(201, array('entity_id' => $photoDetails['name']));
            }
        }
        return $this->createResponse(400);
    }

    public function getIncidentMVDPhotoAction($incidentPhotoId) {
        $photo = Photo::getPhotoByPhotoName($incidentPhotoId);
        return $this->createImageResponse($photo->path);
    }

    public function deleteIncidentMVDPhotoAction($incidentPhotoId) {
        $photo = Photo::getPhotoByPhotoName($incidentPhotoId);

        if ($photo->delete()) {
            $this->storageManager->deletePhoto($photo);
            return $this->createResponse(204);
        }
        return $this->createResponse(304);
    }

}
