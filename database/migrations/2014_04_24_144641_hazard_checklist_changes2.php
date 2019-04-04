<?php

use Illuminate\Database\Migrations\Migration;

class HazardChecklistChanges2 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{   
            $toChange = array(
                    'Hoising or mobbing load overhead'=>'Hoisting or mobbing load overhead',
                    'Tie of with harness'             =>'Tie off with harness',
                    'Harness/Lanyard inspected'       =>'Harness and lanyard inspected',
                    'Operating tele handler'          =>'Operating telehandler',
                    'Hot work of electrical permit required'        =>'Hot work or electrical permit required',
                    'Working on/Near energized equipment'           =>'Working on or near energized equipment',
                    'Inviting expert present'                       =>'Invited expert to present safety topics',
                    'Working on/Near energized equipment'           =>'Working on or near energized equipment',
                    'Diesel engine require pas'                     =>'Diesel engine requires positive air shutoff (PAS)'
            );
            
            foreach ($toChange as $from=>$to){
                HazardChecklistItem::rename($from,$to);
            }
            HazardChecklistItem::where('flha_hazard_item_id','>',187)->delete();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            $toChange = array(
                    'Hoising or mobbing load overhead'=>'Hoisting or mobbing load overhead',
                    'Tie of with harness'             =>'Tie off with harness',
                    'Harness/Lanyard inspected'       =>'Harness and lanyard inspected',
                    'Operating tele handler'          =>'Operating telehandler',
                    'Hot work of electrical permit required'        =>'Hot work or electrical permit required',
                    'Working on/Near energized equipment'           =>'Working on or near energized equipment',
                    'Inviting expert present'                       =>'Invited expert to present safety topics',
                    'Working on/Near energized equipment'           =>'Working on or near energized equipment',
                    'Diesel engine require pas'                     =>'Diesel engine requires positive air shutoff (PAS)'
            );
            foreach ($toChange as $to=>$from){
                HazardChecklistItem::rename($from,$to);
            }
	}

}