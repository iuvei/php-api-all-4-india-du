<?php
// https://tradingdiario.com/wap/#/tgkline?name=DPHARMA-C5&code=0183&if_zhishu=0
header('Access-Control-Allow-Origin:*');
require '../framework/bootstrap.inc.php';

$code = $_GET['code'];




//从数据库查
 $res =  pdo_fetch("select r.*,s.stock_code as code,s.stock_name as name,s.stock_gid as gid,s.id as sid,s.increase_ratio as hcrate from real_time_data r left join stock s on s.stock_code = r.stock_code  where r.stock_code = '".$code."' order by r.id desc ");
//  pdo_debug();
 if($res){
     
     if(time()-strtotime($res['add_time']) > 60*1){
        //查网上
        $url = "https://marketservices.indiatimes.com/marketservices/companyshortdata?companyid=$code&companytype=equity";
        $data = file_get_contents($url);
        
        $data = json_decode($data);
        $info = $data->bse;
        // print_r($info->current);
        if($info->current >0){
            $res['volume'] =  $info->volume;
            
            $where['stock_code'] = $code;
            //更新real_time_data
            $data_update['close'] =   $res['close'] =  $info->current;
            $data_update['high'] =   $res['high'] =  $info->high;
            $data_update['low'] =   $res['low'] =  $info->open;
            $data_update['open'] =   $res['open'] =  $info->open;
            $data_update['add_time'] =   date("Y-m-d H:i:s");
            $update=  pdo_update("real_time_data",$data_update,$where);
            
            //更新stock
            $stock_update['increase_ratio'] = $info->percentChange;
            pdo_update("stock",$stock_update,$where);
            // die();
        }
     }
    
    
     $res['status'] =1;
     $res['newPrice'] =1;
     $res['open'] =$res['close'];
     $res['open_px'] =$res['close'];
     $res['id'] =$res['sid'];
     $res['nowPrice'] =$res['close'];
     $res['today_max'] =$res['close'];
     $res['today_min'] =$res['close'];
     $res['preclose_px'] =$res['close'];
     $res['type'] ='mys';
     $res['business_amount'] =$res['volume'];
    
      die(json_encode($res));
  }

