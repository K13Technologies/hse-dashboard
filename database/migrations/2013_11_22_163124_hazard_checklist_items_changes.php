<?php

use Illuminate\Database\Migrations\Migration;

class HazardChecklistItemsChanges extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            HazardChecklistItem::rename(' Hot work of electrical permit required','Hot work electrical permit required');
            HazardChecklistItem::rename(' Compressed gas - away from ignition','Compressed gas (away from ignition)');
            HazardChecklistItem::rename(' Fire extinguishers - in place','Fire extinguishers (in place)');
            HazardChecklistItem::rename(' Scaffold(inspected/tagged)','Scaffold (inspected/tagged)');
            HazardChecklistItem::rename(' 100% tie with harness','100% tie in with harness');
            HazardChecklistCategory::rename('Camp/Driving','Camp & Driving');
            HazardChecklistItem::rename(' Motor vehicles 7.5m away from facilities or hydrocarbon sources','Motor vehicles 7.5 away from facilities/hydrocarbon sources');
            HazardChecklistCategory::rename('Ergonomic Hazards','Ergonomics');
            HazardChecklistItem::rename(' Lift to heavy/awkward','Lifting heavy/awkward objects');
            HazardChecklistCategory::rename('Driving Hazards','Driving');
            HazardChecklistItem::rename(' Always use spotter','Use spotter when back in up vehicle');
            HazardChecklistItem::rename(' Report all wildlife sightings','Wildlife sightings');
            HazardChecklistCategory::rename('Personal Limitations/Hazards','Personal Limitations');
            HazardChecklistItem::rename(' Mental/Physical/Medical Limitations','Mental limitation');
            HazardChecklistItem::add('Physical Limitations',1);
            HazardChecklistItem::add('Medical Limitations',1);
            HazardChecklistItem::rename(' Grinders condition/use','Grinder condition');
            HazardChecklistItem::rename(' Come along condition/use','Come along condition');
            HazardChecklistItem::rename(' Cutting torch condition/use','Cutting torch condition');
            HazardChecklistItem::rename(' Faulty/defective removed from service','Removed faulty/defective tool ');
            HazardChecklistCategory::rename('Access/Egress','Access & Egress');
            HazardChecklistItem::rename(' Evacuation plan (alarms','Evacuation plan (alarms,routes)');
            HazardChecklistItem::remove('routes)');
            HazardChecklistItem::rename(' Hoisting (tools','Hoisting tools & equipment');
            HazardChecklistItem::remove('equipment)');
            HazardChecklistItem::rename(' Power supplies - generator','Power supplies (generator)');
            HazardChecklistItem::rename(' Moving/rotating equipment','Moving and rotating equipment');
            HazardChecklistItem::rename(' Discussing H & S Issues','Discussing health and safety challenges');
            HazardChecklistCategory::rename('Leadership/Supervision ','Leadership & Supervision ');
            HazardChecklistItem::rename(' Inviting Expert present','Inviting expert to present');
            HazardChecklistItem::rename(' Coaching Co Worker','Coaching co-worker');
            
            HazardChecklistItem::all()->each(function($trade){
                    $trade->formatString();
                    $trade->save();
            });
	}
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}