<?php
class Zend_View_Helper_LoggedInAs extends Zend_View_Helper_Abstract
{
	public function loggedInAs ()
	{
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			$user = $auth->getIdentity();
			if (!isset($user->username)) {
				$auth->clearIdentity();
				$info = 'logout';
				return $info;				
			}		
			
			$logoutUrl = $this->view->url(array('controller'=>'auth',
                'action'=>'logout'), null, true);
			$url =$this->view->url(array('controller'=>'user',
			                            'action'=>'edit',
					   					'id'=>$user->id));
				
			$info = '<div class ="menuButton"><span class="menu">'.$user->username.'</span>';
			$info .= '<ul> 
					<li><a href="'.$url.'">Mon profil</a></li>
					<li class="separator">​</li>
					<li><a href="'.$logoutUrl.'" class="logout">se déconnecter</a></li>
					</ul></div>';
			
			
			return $info;
		}

		$request = Zend_Controller_Front::getInstance()->getRequest();
		$controller = $request->getControllerName();
		$action = $request->getActionName();
		if($controller == 'auth' && $action == 'index') {
			return '';
		}
		$form = new Application_Form_Login();;
		$loginUrl = $this->view->url(array('controller'=>'auth',
		                'action'=>'index'), null, true);
        $info = '<div class ="menuButton"><span class="menu"> Se connecter </span><ul><li class="form">'.$form->setAction($loginUrl).'</li></ul></div>';
		return $info;
		//$loginUrl = $this->view->url(array('controller'=>'auth', 'action'=>'index'));
		//return '<a href="'.$loginUrl.'">Login</a>';
	}
}
