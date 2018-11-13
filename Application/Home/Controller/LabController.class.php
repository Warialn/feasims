<?php
namespace Home\Controller;
use Think\Controller;
class LabController extends Controller {
	//机房注册
    public function lab_register(){

        if($_POST){
            $data['device_id'] = $_POST['SERIAL_NO'];
            $res = M('device_property')->where(array('device_id'=>$data['device_id'],'device_status'=>1))->select();
            
            if($res){
            	$house_res = M('front_end_device')->where(array('device_id'=>$res[0]['device_id']))->select();
            	foreach ($house_res as $key => $value) {
            		$house_id .= $value['house_id'].',';
            	}
            	foreach ($res as $key => $value) {
            		$device_type .= $value['device_type'].','; 
            	}
            	$house_id = substr($house_id, 0,strlen($house_id)-1);
            	$device_type = substr($device_type, 0,strlen($device_type)-1);
            	$buffer = 'SERIAL_NO:'.$res[0]['device_id'].'|'.'DEVICE_IP:'.$res[0]['device_ip'].'|'.'HOUSE_ID:'.$house_id.'|'.'CMP_IP:'.$_POST['CMP_IP'].'|'.'DEVICE_TYPE:'.$device_type;
            	echo $buffer; 
            }else{
            	$data['device_ip'] = $_POST['DEVICE_IP'];
	            $data['lab_id'] = $_POST['HOUSE_ID'];
	            $one_house_id = str_replace('[', '', $data['lab_id']);
	            $two_house_id = str_replace(']', '', $one_house_id);
	            $house_id = str_replace("'", '', $two_house_id);
	            $three_type = $_POST['DEVICE_TYPE'];
	            $one_type = str_replace('[', '', $three_type);
	            $two_type = str_replace(']', '', $one_type);
	            $type = str_replace("'", '', $two_type);
	            $type = explode(',',$type);
	            $data['device_status'] = 1;
	            foreach ($type as $key => $value) {
	            	$data['device_type'] = $value;
	            	M('device_property')->add($data);
	            }
	            $one_device_type = str_replace('[','',$_POST['DEVICE_TYPE']);
	            $two_device_type = str_replace(']','',$one_device_type);
	            $device_type = str_replace("'",'',$two_device_type);

	            $buffer = 'SERIAL_NO:'.$_POST['SERIAL_NO'].'|'.'DEVICE_IP:'.$_POST['DEVICE_IP'].'|'.'HOUSE_ID:'.$house_id.'|'.'CMP_IP:'.$_POST['CMP_IP'].'|'.'DEVICE_TYPE:'.$device_type;
	            echo $buffer;
            }
            


        }
    }
}
