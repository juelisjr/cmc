<?php
/**
 * Compojoom System Plugin
 * @package Joomla!
 * @Copyright (C) 2012 - Yves Hoppe - compojoom.com
 * @All rights reserved
 * @Joomla! is Free Software
 * @Released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
 * @version $Revision: 1.0.0 $
 **/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// import libaries
jimport('joomla.event.plugin');

JLoader::discover('CmcHelper', JPATH_ADMINISTRATOR . '/components/com_cmc/helpers/');   // Hmm not working?


class plgSystemECom360_payplans extends JPlugin {


    /**
     * $appCompleteHtml = PayplansHelperEvent::trigger('onPayplansPaymentAfter',$args,'payment',$payment);
     */

    public function onPayplansPaymentAfter($data){
        $this->notifyMC($data);
    }

    /**
     *
     * @param $data
     */
    function notifyMC($data) {
        $session = JFactory::getSession();
        $mc = $session->get( 'mc', '0' );

        // Trigger plugin only if user comes from Mailchimp
        if(!$mc) {
            return;
        }

        //  $order      = PayplansOrder::getInstance( $payment->getOrder())
        /*
          // Table fields
            protected $payment_id;
            protected $user_id;
            protected $invoice_id;
            protected $app_id;
            protected $created_date;
            protected $modified_date;
            protected $params;
            protected $gateway_params;
            protected $_transactions	=	array();
         */


        $mc_cid = $session->get('mc_cid', '');
        $mc_eid = $session->get('mc_eid', '');

        $params = JComponentHelper::getParams('com_cmc');
        $shop_name = $params->get("shop_name", "Your shop");
        $shop_id = $params->get("shop_id", 42);

        $db = JFactory::getDbo();
        $sql = "SELECT * FROM #__categories WHERE id = " . $event->catid;

        $db->setQuery($sql);
        $cat = $db->loadObject();

        $products = array( 0 => array(
            "product_id" => $event->id, "sku" => $event->semnum, "product_name" => $event->title,
            "category_id" => $event->catid, "category_name" => $cat->title, "qty" => $row->nrbooked,
            "cost" =>  $event->fee
            )
        );

        CmcHelperEcom360::sendOrderInformations($mc_cid, $mc_eid, $shop_id, $shop_name, $row->id, $row->payment_brutto,
            $row->payment_tax, 0.00, $products // No shipping
        );
    }
}