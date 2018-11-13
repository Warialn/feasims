<?php
namespace Home\Controller;
use Think\Controller;
use Think\Tree;
class ContentController extends CommonController {
	public function index(){
		$res = M('content_collection_configure')->select();
		foreach ($res as $key => $value) {
			$res[$key]['ftp_type'] = $this->info_value('ftp_type',$value['ftp_type']);
		}
		$this->assign('res',$res);
		Layout('Layout/layout');
      	$this->display();
	}
	public function content_add(){
		if($_GET){
			$data = $_GET;
			$res = M('content_collection_configure')->add($data);
			if($res){
				A('Home/Common')->log('Web', '添加内容参数', 5);
				$this->ajaxReturn(array('status'=>'success'));
			}
		}
	}
	public function content_edit(){
		if($_GET){
			$id = I('id');
			$data = $_GET['data'];
			$res = M('content_collection_configure')->where(array('id'=>$id))->save($data);
			if($res){
				A('Home/Common')->log('Web', '编辑内容参数', 5);
				$this->ajaxReturn(array('status'=>'success'));
			}else{
				A('Home/Common')->log('Web', '编辑内容参数', 3);
				$this->ajaxReturn(array('status'=>'error','info'=>'编辑失败'));
			}
		}
	}
	public function content_del(){
		if($_GET){
			$id = I('id');
			$res = M('content_collection_configure')->where(array('id'=>$id))->delete();
			if($res){
				A('Home/Common')->log('Web', '删除内容参数', 5);
				$this->ajaxReturn(array('status'=>'success'));
			}

		}
	}
	//参数管理
    public function info_value($col,$value){
        $c = [
            'ftp_type' => function($v){
                $cc = [
                  '1'                                   => '文件还原ZIP包',
                  '2'                                   => 'XDR话单',
                 
                ];
                return isset($cc[$v]) ? $cc[$v] : $v;
            },  
        ];
        return isset($c[$col]) ? $c[$col]($value) : $value;
    }  
    public function test(){
    	$data = array(a,a,b,c,b,f,g,g,f,c,d,b,n,m,l,l,l,m,l);
    	$new = array();
    	$new = array_count_values($data);
		arsort($new);
    	/*foreach ($new as $key => $value) {
    		
    	}*/
    	dump($new);die;
    }
 }