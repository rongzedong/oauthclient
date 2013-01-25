<?php

/***************************************************************************
 *
 * Copyright (c) 2013 rong zedong
 *
 **************************************************************************/

/**
 * class_taobao_api.php
 * 
 * 
 * @package	oauthclient
 * @author	rongzedong@msn.com
 * @version	1.0
 * history 
 * 2013.1.16 rong zedong.
 **/

class api_taobao
{

	var $fields = array(
		'taobaoke.items.get' 		=> 'num_iid,title,nick,pic_url,price,click_url,commission,commission_rate,commission_num,commission_volume,shop_click_url,seller_credit_score,item_location,volume',
		'taobaoke.shops.get' 		=> 'user_id,click_url,shop_title,commission_rate,seller_credit,shop_type,auction_count,total_auction',
		'taobaoke.report.get' 		=> 'trade_parent_id,trade_id,real_pay_fee,commission_rate,commission,app_key,outer_code,pay_time,pay_price,num_iid,item_title,item_num,category_id,category_name,shop_title,seller_nick',
		'taobaoke.items.detail.get' => 'click_url,shop_click_url,seller_credit_score,detail_url,num_iid,title,nick,desc,auction_point,cid,input_str,pic_url,location,price,express_fee,freight_payer,has_invoice,has_warranty,modified,product_id,item_imgs,videos',
		'taobaoke.items.relate.get' => 'num_iid,title,nick,pic_url,price,click_url,commission,ommission_rate,commission_num,commission_volume,shop_click_url,seller_credit_score,item_location,volume',
		'taobaoke.shops.relate.get' => 'user_id,seller_nick,shop_id,shop_title,seller_credit,shop_type,commission_rate,click_url,total_auction,auction_count',
		'taobaoke.items.coupon.get' => 'num_iid,title,nick,pic_url,price,click_url,commission,commission_rate,commission_num,commission_volume,shop_click_url,seller_credit_score,item_location,volume,coupon_price,coupon_rate,coupon_start_time,coupon_end_time,shop_type',
		'user.buyer.get' 			=> 'user_id,nick,sex,buyer_credit,avatar,has_shop,vip_info',
		'item.get' 					=> 'num_iid,title,nick,desc,auction_point,cid,input_str,pic_url,location,price,express_fee,freight_payer,has_invoice,has_warranty,modified,product_id,item_imgs,videos',
		'items.list.get' 			=> 'detail_url,num_iid,title,nick,desc,auction_point,cid,input_str,pic_url,location,price,express_fee,freight_payer,has_invoice,has_warranty,modified,product_id,item_imgs,videos',
		'itempropvalues.get' 		=> 'cid,pid,prop_name,vid,name,name_alias,status,sort_order',
		'itemcats.get' 				=> 'cid,parent_cid,name,is_parent,status,sort_order',
		'shopcats.list.get' 		=> 'cid,parent_cid,name,is_parent',
		'shop.get' 					=> 'sid,cid,nick,title,desc,bulletin,pic_path,created,modified,shop_score',

		);

	var $home_url 		= 'http://www.oo8h.com/';
	var $api_url 		= 'https://eco.taobao.com/router/rest';
	var $op;

	function call($action, $postFields = ''){

		$req = array(
			'format' 		=> 'json',
			'access_token' 	=> $this->op->token['access_token'],
			'method' 		=> 'taobao.'.$action,
			'v' 			=> '2.0',
			);

		if(array_key_exists($action, $this->fields))
			$req['fields'] = $this->fields[$action];

		if(is_array($postFields)) $req = array_merge($req, $postFields);

		$url = $this->api_url . "?" . http_build_query($req, '', '&');
		$rp = $this->op->get($url);
		if($rp->error_response->code >0)
		{
			echo('error code: '.$rp->error_response->code.'; msg: '. $rp->error_response->msg);
			exit;
		}
		$response_name = str_replace('.', '_', $action) . "_response"; 
		return $rp->$response_name;
	}

	public function __construct(&$op) {
		$this->op = &$op;
	}
}

