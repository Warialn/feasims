<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function index(){
      if($_GET){
           $user = M('users');
           $pass = I('password');
           $mcode = I('code');
           $verify = new \Think\Verify();
           if($verify->check($mcode,'')){
              if($_SESSION['status'] == 2){
                  $res = $user->where(array('user_login'=>I('username'),'user_pass'=>md5(I('password'))))->find();
                  if ($res) {
                        
                     session('uid',$res['id']);
                     session('username',$res['user_login']);
                    // setcookie('userid',$res['id'],time()+3600*24);
                     //setcookie('username',$res['user_login'],time()+3600*24);
                     self::update($res['id']);
                    // A('Home/common')->log('Web','登录',5);
                      if(preg_match("/[A-Za-z]/",$pass)&& preg_match("/\d/",$pass)){
                       echo json_encode(array('status' =>'success'));
                      } else{
                       echo json_encode(array('status'=>'warnning'));
                      }              
                  }else{
                      //A('Home/common')->log('Web','登录',3);
                      echo  json_encode(array('status'=>'error'));
                  }
              }elseif($_SESSION['status'] == 0){
              //验证码为空
                   echo json_encode(array('status'=>'error0'));

              }elseif($_SESSION['status'] == 1){
              //验证码已过期
                   echo json_encode(array('status'=>'error1'));
              }elseif($_SESSION['status'] == 3){
            //验证码不正确
                   echo json_encode(array('status'=>'error2'));
              }else{
                //该验证码已失效，请刷新验证码
                echo json_encode(array('status'=>'error3'));
              }

           }
        }else{
           $this->display();
        }

    }
    public function verify(){
       ob_clean();
      $config =    array(
      'imageW'    =>    130,
            'imageH'    =>    42,
            'fontSize'    =>    19,    // 验证码字体大小
            'length'    =>    4,     // 验证码位数
            'useNoise'    =>    false, // 关闭验证码杂点
            'useCurve'    =>    false,
            'bg'        =>    array(208, 238, 238)
    );
    $verify = new \Think\Verify($config);
    $ver=$verify->entry();

    }

    public function update($id){
       $user = M('users');
       $data['last_login_time'] = time();
       $data['last_login_ip'] = get_client_ip();
       $user->where(['id'=>$id])->save($data);
    }
   
    public function logout(){
          $_SESSION = array(); //清除SESSION值.
          setcookie(session_name(),'',time()-1,'/');
          session_destroy();  //清除服务器的sesion文件
          $this->redirect('Login/index');
    }
}