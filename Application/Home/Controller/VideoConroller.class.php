<?php
namespace Home\Controller;
use Think\Controller;
use Think\Tree;
class VideoController extends CommonController {
   
    public function video_first_trial(){
        Layout('Layout/layout');
        $this->display();
        
    }
    public function video_re_trial(){
        Layout('Layout/layout');
        $this->display();
        
    }
    public function video_nosure(){
        Layout('Layout/layout');
        $this->display();
        
    }
    public function video_result(){
        Layout('Layout/layout');
        $this->display();
        
    }
    
}