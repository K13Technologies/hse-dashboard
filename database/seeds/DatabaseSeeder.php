<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

		$this->call('TradeSeeder');
                
		$this->call('OrganizationalStructuresSeeder');
                
		$this->call('HazardCategorySeeder');
                
		$this->call('HazardActivitySeeder');
                
                $this->call('POCategorySeeder');
                
		$this->call('POActivitySeeder');
                
		$this->call('HazardChecklistSeeder');
		
                $this->call('UserRoleSeeder');
                
                $this->call('ActivitySeeder');
                
                $this->call('IncidentTypeSeeder');
                
                $this->call('IncidentSchemaSeeder');
                
//                $this->call('WorkerSeeder');
	}

}

class TradeSeeder extends DatabaseSeeder {
    
        public function run()
	{
            if(!Trade::all()->count()){
                $lines = file(Config::get('api::storagePaths.professionList'));
                foreach ($lines as $line){
                    $line = trim(str_replace('\n', '', $line));
                    Trade::create(array('trade_name'=>$line));
                }
            }
	}
}

class OrganizationalStructuresSeeder extends DatabaseSeeder {
    
        public function run()
	{
            if(!Company::all()->count()){
            
                Group::whereRaw('1')->delete();
                BusinessUnit::whereRaw('1')->delete();
                Division::whereRaw('1')->delete();
                Company::whereRaw('1')->delete();

                $rand = rand(3,4);
                for ($i=0;$i<$rand;$i++){
                    //company
                    $company = new Company;
                    $company->company_name = 'Company '.$i;
                    $company->save();

                    for ($j=0;$j<$rand;$j++){
                        //division
                        $division = new Division;
                        $division->company_id = $company->company_id;
                        $division->division_name = 'Division '. $j . ' ' . $division->company_id;
                        $division->save();

                        for ($k=0;$k<$rand;$k++){
                            //business_unit
                            $bu = new BusinessUnit;
                            $bu->division_id = $division->division_id;
                            $bu->business_unit_name = 'Business Unit '. $k . ' ' . $bu->division_id;
                            $bu->save();

                            for ($l=0;$l<$rand;$l++){
                                //groups
                                $group = new Group;
                                $group->business_unit_id = $bu->business_unit_id;
                                $group->group_name = 'Group '. $l . ' ' . $group->business_unit_id;
                                $group->save();
                                
                            }//end for groups
                        }//end for business_units
                    }//end for divisions
                }//end for companies
            }//end if 
        }//end function run
 
}//end class

class WorkerSeeder extends DatabaseSeeder {
    
        public function run()
	{
            for ($i=0;$i<rand(1, 10);$i++){
                    $group = Group::orderBy(DB::raw('RAND()'))->get()->first();
                    
                    $worker = new Worker;
                    
                    $worker->setGroupId($group->group_id);
                    $worker->auth_token = Worker::generateAuthToken();
                    $worker->api_key = str_random(255);
                    $worker->profile_completed = rand(0,255)%2;
                    
                    $worker->save();
            }//end for
	}//end run
        
}//end class

class HazardActivitySeeder extends DatabaseSeeder {
    
        public function run()
        {
            if(!HazardActivity::all()->count()){
                $lines = file(Config::get('api::storagePaths.hazardActivityList'));
                foreach ($lines as $line){
                    $line = trim(str_replace('\n', '', $line));
                    HazardActivity::create(array('activity_name'=>$line));
                }
            }
	}
}//end class


class HazardCategorySeeder extends DatabaseSeeder {
    
        public function run()
        {
            if(!HazardCategory::all()->count()){
                $lines = file(Config::get('api::storagePaths.hazardCategoryList'));
                foreach ($lines as $line){
                    $line = trim(str_replace('\n', '', $line));
                    HazardCategory::create(array('category_name'=>$line));
                }
            }
	}
}//end class


class POActivitySeeder extends DatabaseSeeder {
    
        public function run()
        {
            if(!PositiveObservationActivity::all()->count()){
                $lines = file(Config::get('api::storagePaths.positiveObservationActivityList'));
                foreach ($lines as $line){
                    $line = trim(str_replace('\n', '', $line));
                    PositiveObservationActivity::create(array('activity_name'=>$line));
                }
            }
	}
}//end class

class ActivitySeeder extends DatabaseSeeder {
    
        public function run()
        {
            if(!Activity::all()->count()){
                $lines = file(Config::get('api::storagePaths.activities'));
                foreach ($lines as $line){
                    $line = trim(str_replace('\n', '', $line));
                    Activity::add($line);
                }
            }
	}
}//end class


class IncidentTypeSeeder extends DatabaseSeeder {
    
        public function run()
        {
            if(!IncidentType::all()->count()){
                $lines = file(Config::get('api::storagePaths.incidentTypes'));
                foreach ($lines as $line){
                    $line = trim(str_replace('\n', '', $line));
                    IncidentType::add($line);
                }
            }
	}
}//end class


class POCategorySeeder extends DatabaseSeeder {
    
        public function run()
        {
            if(!PositiveObservationCategory::all()->count()){
                $lines = file(Config::get('api::storagePaths.positiveObservationCategoryList'));
                foreach ($lines as $line){
                    $line = trim(str_replace('\n', '', $line));
                    PositiveObservationCategory::create(array('category_name'=>$line));
                }
            }
	}
}//end class

class HazardChecklistSeeder extends DatabaseSeeder {
    
        public function run()
        {   
            if(!HazardChecklistCategory::all()->count()){
                $lines = file(Config::get('api::storagePaths.hazardChecklistCategoriesAndItems'));
                foreach ($lines as $line){
                    $line = str_replace('\n', '', $line);
                    $sections = explode('|', $line);
                    if (count($sections)==2)
                    $categoryName = $sections[0];
                    
                    $c = new HazardChecklistCategory();
                    $c->category_name = $categoryName;
                    $c->save();
                    
                    $items = explode(',',$sections[1]);
                    foreach ($items as $item){
                        $i = new HazardChecklistItem();
                        $i->item_name = $item;
                        $i->flha_hazard_category_id = $c->flha_hazard_category_id;
                        $i->save();
                    }
                }
            }
	}
}//end class