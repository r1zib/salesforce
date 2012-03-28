<?php

class Application_Form_Template extends Zend_Form
{

    public function init()
    {
    	
    	$template = 'nke/template';
    	    	
        $file =  new Zend_Form_Element_File('template');
        $file->setLabel('Télécharger un template :')
        ->setDestination(APPLICATION_PATH.'/../var/upload'.$template);
        
        // ensure only 1 file
        $file->addValidator('Count', false, 1);
        
        $this->addElement($file);
        
        $envoyer = new Zend_Form_Element_Submit('envoyer');
        $envoyer->setAttrib('id', 'boutonenvoyer');
        $this->addElement($envoyer);
        
        
    }


}

