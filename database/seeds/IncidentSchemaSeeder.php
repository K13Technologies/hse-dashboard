<?php

class IncidentSchemaSeeder extends DatabaseSeeder {
    
        public function run()
	{   
            if(!IncidentSchema::all()->count()){
                $dir = Config::get('api::storagePaths.incidentSchemas');
                $files = array_diff(scandir($dir), array('..', '.'));
                foreach ($files as $f){
                    $type = IncidentSchema::getType($f);
                    $filepath = $dir.'/'.$f;
                    $plistDocument = new DOMDocument();
                    $plistDocument->load($filepath);
                    $plistValue = PListParser::parsePlist($plistDocument);
                    IncidentSchema::createFromPlist($plistValue,$type);
                }
            }
        }
}