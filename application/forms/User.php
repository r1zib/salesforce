<?php

class Application_Form_User extends Zend_Form
{

    public function init()
    {
 		$this->setName("User");
        $this->setMethod('post');
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->addFilter('Int');
        $this->addElement($id);
        
        $this->addElement('text', 'username', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(
                array('StringLength', false, array(0, 50)),
            ),
            'required'   => true,
            'label'      => 'Username:',
        ));

        $this->addElement('text', 'password', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(0, 50)),
            ),
            'required'   => true,
            'label'      => 'Password:',
        ));
        
        $this->addElement('text', 'token', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
        		array('StringLength', false, array(20, 50)),
        			),
            'required'   => true,
            'label'      => 'Token :',
        ));
        $role = new Zend_Form_Element_Select('role');
        $role->setLabel('role :')
             ->setMultiOptions(array('admistrator'=> 'admistrator',
                              'user'=> 'user'));
        $this->addElement($role);
        
        $file =  new Zend_Form_Element_File('wsdl');
        $file->setLabel('Télécharger le WSDL spécifique à votre organisation :')
        ->setDestination(APPLICATION_PATH.'/../var/upload');
        
        // ensure only 1 file
        $file->addValidator('Count', false, 1);
        
        $this->addElement($file);
        
        $envoyer = new Zend_Form_Element_Submit('envoyer');
        $envoyer->setAttrib('id', 'boutonenvoyer');
        $this->addElement($envoyer);
        
        
    }


}

