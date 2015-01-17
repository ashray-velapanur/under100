<?php
class GaussDev_Multilist_IndexController extends Mage_Core_Controller_Front_Action{
	/* @var GaussDev_Multilist_Helper_Data */
	private $helper;

	public function _construct(){
		$this->helper=Mage::helper('GaussDev_Multilist');
	}


    public  function createListAction(){
		$result=false;
    	$session = Mage::getSingleton('customer/session');
    	if(!$session->isLoggedIn()) exit("Not logged in."); //user isnt logged in.

    	$uid= $session->getId();
    	//$test=$this->helper->getItemLists($uid,$this->getRequest()->getPost('itemID'));

    	$lists=$this->getRequest()->getPost('list', array());
		$itemID = $this->getRequest()->getPost('itemID');
		$listName = trim($this->getRequest()->getPost('listName'));
		if($itemID) {
    		if($listName) {
    			$listID=$this->helper->createList($uid, $listName);
    			$lists[]=$listID;
    			if(!$listID) exit(500);
    		}

    		$this->helper->flashItem($itemID);	//remove item from all lists

			foreach ($lists as $listID) {
				if ($this->helper->additem($listID, $itemID)) {
					$result = true;
				} else {
					exit(500);
				}
			}

    		if($result){
    			$availableLists= $this->helper->getListInfo($uid);
                echo '<p class="item-added-to-list">Item has been added to your list.</p>';
    			foreach ($availableLists as $list){



    				echo '<li>';
                   	echo '<input type="checkbox" ';
                  	if(in_array($list['id'], $lists)) echo 'checked="checked" ';
                  	echo ' class="listCheckbox" value="'.$list['id'].'" name="list['.$list['id'].']" id="'.$list['id'].'">
                        <label for="'.$list['id'].'">'.Mage::helper('core')->escapeHtml($list['name']).'</label>
                   		</li>'; //</span></div>

    			}
    			echo '<li class="add-list-text-input">
                                                <input type="text" name="listName" value="" id="listName" title="Create new list" placeholder="Create new list">
                                            </li>
    					';
				
                echo '<script>jQuery(function(){jQuery(".listCheckbox").uniform();jQuery.uniform.update();});</script>';

    		} else {
    			$availableLists= $this->helper->getListInfo($uid);
    			$lists=array();    foreach ($this->helper->getItemLists($uid,$itemID) as $l) $lists[]=$l['list_fk'];
    			foreach ($availableLists as $list){
    				echo '<li>';
    				echo '<input type="checkbox" ';
    				if(in_array($list['id'], $lists)) echo 'checked="checked" ';
    				echo ' class="listCheckbox" value="'.$list['id'].'" name="list['.$list['id'].']" id="'.$list['id'].'">
                        <label for="'.$list['id'].'">'.Mage::helper('core')->escapeHtml($list['name']).'</label>
                   		</li>'; //</span></div>
    			}
    			echo '<li class="add-list-text-input">
                                                <input type="text" name="listName" value="" id="listName" title="Create new list" placeholder="Create new list">
                                            </li>';

    			echo '<script>jQuery(function(){jQuery(".listCheckbox").uniform();jQuery.uniform.update();});</script>';
    		}
    		echo '<input type="hidden" name="pID" id="pID" value="" />';
    	} else {
    		exit(500);
    	}

    }

    public function getListsAction(){
    	$session = Mage::getSingleton('customer/session');
    	if(!$session->isLoggedIn()) exit("Not logged in."); //user isnt logged in.
    	
    	$uid= $session->getId();
    	//$test=$this->helper->getItemLists($uid,$this->getRequest()->getPost('itemID'));
    	
    	$itemID = $this->getRequest()->getParam('itemID');
    	
    	$lists=array();
    	$listHelper= &$this->helper;
    	foreach ($listHelper->getItemLists('',isset($itemID) ? $itemID : null) as $l) $lists[]=$l['list_fk'];
    	 
    	
    	 foreach ($listHelper->getListInfo() as $list):
    		echo '<li>';
    	    echo '<input type="checkbox" ';if(in_array($list['id'], $lists)): echo 'checked="checked" ';  endif;
    	    echo 'class="listCheckbox" value="'.$list['id'].'" name="list['. $list['id'].']" id="'.$list['id'].'">';
    	    echo '<label for="'.$list['id'].'">'. Mage::helper('core')->escapeHtml($list['name']).'</label></li>';
		endforeach;
    	echo '<li class="add-list-text-input">';
    	echo '<input type="text" name="listName" value="" id="listName" title="Create new list" placeholder="Create new list">
    	    	 		</li>
    	    	 		<input type="hidden" name="pID" id="pID" value="'.$itemID.'" />';
    	
    	echo '<script>jQuery(function(){jQuery(".listCheckbox").uniform();jQuery.uniform.update();});</script>';
    	
    	
    	
    	
    }
    
    
    
	public function createAction()
	{
		if (!$this->_validateFormKey()) {
			throw new Exception('Invalid Form Key.');
		}
		$name = $this->getRequest()->getPost('name');
		$image = $_FILES['image']['error'] === 0 ? $_FILES['image'] : null;
		if ($name) {
			$created = Mage::helper('GaussDev_Multilist')->createList(Mage::getSingleton('customer/session')
																		  ->getCustomerId(), $name);
			if ($created && $image) {
				Mage::helper('GaussDev_Multilist')
					->changeImage($created, file_get_contents($image['tmp_name']), $image['type']);
			}
		}
		$this->_redirect(null, array('_direct' => 'profile', '_fragment' => 'lists'));
	}

	public function updateAction()
	{
		if (!$this->_validateFormKey()) {
			throw new Exception('Invalid Form Key.');
		}
		$name = $this->getRequest()->getPost('name');
		$id = $this->getRequest()->getPost('id');
		$image = $_FILES['image']['error'] === 0 ? $_FILES['image'] : null;
		if ($name && $id) {
			$created = Mage::helper('GaussDev_Multilist')->editList(Mage::getSingleton('customer/session')
																		->getCustomerId(), $name, $id);
			if ($created && $image) {
				Mage::helper('GaussDev_Multilist')
					->changeImage($id, file_get_contents($image['tmp_name']), $image['type']);
			}
		}
		$this->_redirect(null, array('_direct' => 'profile', '_fragment' => 'lists'));
	}

	public function ajaxDeleteItemAction()
	{
		$listId = $this->getRequest()->getPost('listId');
		$itemId = $this->getRequest()->getPost('itemId');
		if ($listId && $itemId) {
			echo (int)Mage::helper('GaussDev_Multilist')->deleteItem($listId, $itemId);
		} else {
			echo 0;
		}

	}
}
