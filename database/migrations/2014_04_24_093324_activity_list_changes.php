<?php

use Illuminate\Database\Migrations\Migration;

class ActivityListChanges extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                $toAdd = array('Environmental','Other','Safety Services','Maintenance Services',
                               'Hydrovac Operations','Vacuum Operations','Combo Unit Operations',
                               'Water Hauling Operations','Crane Operations','Heavy Equipment Operations',
                                'Excavation Operations');
                
                foreach ($toAdd as $activityName){
                    HazardActivity::add($activityName);
                    PositiveObservationActivity::add($activityName);
                }
                $toRename = array('Medical'=>'Medical Services');
                
                foreach ($toRename as $from => $to){
                    HazardActivity::rename($from, $to);
                    PositiveObservationActivity::rename($from, $to);
                }
                
                PositiveObservationActivity::rename('Reclamation','Reclamation & Remediation');
                
                $toAddForPO = array('Offsite - Pipelines','Offsite - Facilities','Construction - Facility',
                                    'Construction - Lease','Construction - Roads','Transportation' );
                
                foreach ($toAddForPO as $activityName){
                    PositiveObservationActivity::add($activityName);
                }
                
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
                $toAdd = array('Environmental','Other','Safety Services','Maintenance Services',
                               'Hydrovac Operations','Vacuum Operations','Combo Unit Operations',
                               'Water Hauling Operations','Crane Operations','Heavy Equipment Operations',
                                'Excavation Operations');
                
                foreach ($toAdd as $activityName){
                    HazardActivity::remove($activityName);
                    PositiveObservationActivity::remove($activityName);
                }
                
                $toRename = array('Medical'=>'Medical Services');
                
                foreach ($toRename as $to => $from){
                    HazardActivity::rename($from, $to);
                    PositiveObservationActivity::rename($from, $to);
                }
                PositiveObservationActivity::rename('Reclamation & Remediation','Reclamation');
                
                $toAddForPO = array('Offsite - Pipelines','Offsite - Facilities',
                                    'Construction - Facility','Construction - Lease','Construction - Roads','Transportation'                   
                );
                foreach ($toAddForPO as $activityName){
                    PositiveObservationActivity::remove($activityName);
                }
	}

}