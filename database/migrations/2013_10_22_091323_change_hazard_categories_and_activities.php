<?php

use Illuminate\Database\Migrations\Migration;

class ChangeHazardCategoriesAndActivities extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            HazardCategory::rename('Psychological', 'Violence & Bullying');
            HazardCategory::rename('Occupational health & hygiene', 'Biological');
            HazardCategory::rename('Leadership', 'People');
            HazardCategory::rename('Hazard Awareness', 'New & Young worker');
            HazardCategory::add('Security');
            HazardCategory::add('Weather');
            
            HazardActivity::rename('Reclamation','Reclamation & Remediation');
            HazardActivity::rename('Construction - Road','Construction - Roads');
            HazardActivity::rename('Competions','Completions');
            HazardActivity::add('Transportation');
	}   

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            HazardCategory::rename('Violence & Bullying','Psychological');
            HazardCategory::rename('Biological','Occupational health & hygiene');
            HazardCategory::rename('People','Leadership');
            HazardCategory::rename('New & Young worker','Hazard Awareness');
            HazardCategory::remove('Security');
            HazardCategory::remove('Weather');
            
            HazardActivity::rename('Reclamation & Remediation','Reclamation');
            HazardActivity::rename('Construction - Roads','Construction - Road');
            HazardActivity::rename('Completions','Competions');
            HazardActivity::remove('Transportation');
	}

}