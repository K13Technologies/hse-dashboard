<?php

return array(
        'invalidRoute' => array('route' => 'Invalid route'),
        'invalidJson' => array('request' => 'Invalid JSON'),
        'authTokenMissing' => array('auth' => 'An authentication token must be provided'),
        'inexistingAuthToken' => array('auth' => "The authentication token provided doesn't exist"),
        'photoJson' => array('photoJson' => 'The json request body must contain the "photo" key'),
    
        'profileIncomplete' => array('profileIncomplete' => "Token found, but profile is incomplete"),
        'tokenAlreadyRegistered' => array('tokenAlreadyRegistered' => "You have already registered"),
    
        'licensePlateAlreadyExists' => array('duplicateLicensePlate' => "A vehicle with that license plate already exists"),
        'identificationNumberAlreadyExists' => array('duplicateIdentificationNumber' => "A vehicle with that identification number already exists"),

        'invalidHazardActivityId' => array('invalidHazardActivityId' => "This hazard activity id doesn't resolve to any existing hazard activities"),
        'missingHazardCategories' => array('missingHazardCategories' => "A list of hazard categories must be provided"),
        'invalidHazardCategories' => array('invalidHazardCategories' => "None of the hazard categories provided exists"),
        'invalidHazardCategoryType' => array('invalidHazardCategoryType' => "The hazard category list field must be an array"),
    
        'invalidPOActivityId' => array('invalidPOActivityId' => "This positive observation activity id doesn't resolve to any existing positive observation activities"),
        'missingPOCategories' => array('missingPOCategories' => "A list of positive observation categories must be provided"),
        'invalidPOCategories' => array('invalidPOCategories' => "None of the positive observation categories provided exists"),
        'invalidPOCategoryType' => array('invalidPOCategoryType' => "The positive observation category list field must be an array"),
        'missingPOpeopleObserved' => array('missingPOpeopleObserved' => "A list of people for this positive observation must be provided"),
        'invalidPOpeopleObservedType' => array('invalidPOpeopleObservedType' => "The positive observation peopleObserved field must be an array"),
        'invalidPOpeopleObserved' => array('invalidPOpeopleObserved' => "None of the people in the observed people list has valid information"),
        
        'missingFlhaHazardItems' => array('missingFlhaHazardItems' => "A list of FLHA hazard items must be provided"),
        'invalidFlhaHazardItemsType' => array('invalidFlhaHazardItemsType' => "The FLHA hazard item field must be an array"),
        'invalidFlhaHazardItems' => array('invalidFlhaHazardItems' => "None of the hazard checklist items provided exists"),

        'journeyInProgress' => array('journeyInProgress' => "Another journey you started is in progress"),
        'noJourneyInProgress' => array('noJourneyInProgress' => "You haven't started any journeys"),
 );



?>
