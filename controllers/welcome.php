<?php

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
  public function makeGame($game){
    if($game){
      $game = new stdClass();
      $uuid = $this->couch->uuids(1)->body->uuids[0];
      echo "<pre>";
      $game->_id = "newGame$uuid";
      $this->couch->create($game);
    }
  }
  public function logout(){
    $this->session->sess_destroy();
  }
	public function index()
	{
	  $this->load->helper('url');
	  $user = $this->session->userdata("user");
	 //echo "user $user user";
	 if($user){
	   
      redirect("/welcome/chat/");
	 }
	  if(!$user && $_POST){
	    $user = $_POST['name'];
	    $this->session->set_userdata(array("user"=>$user));
      header("Location: /ci/index.php/welcome/chat/");
	    
	  }
		$this->load->view('index.html');
	}
	public function chat($name = "anon") {
	$user = $this->session->userdata("user");
	if(!$user){
    header("Location: /ci/index.php/welcome/");
	}
	echo "Welcome $user";
	  $uuid = $this->couch->uuids(1);
	  $moves = $this->couch->get("Game")->move;
	  $this->load->view('chat',compact($name,$moves));
  }
  public function fetch($last_seq = '') {
    header("Content-Type: application/json");
    if($last_seq){
      $seq = $this->couch->get("/_changes?since=$last_seq&feed=longpoll");
      
    }else{
      $seq = $this->couch->get("/_changes");
    }
    $last_seq = $seq->last_seq;
    $doc = $this->couch->get("Game");
    $moves = $doc->move;
    $lastmove = $doc->lastmove;
	echo json_encode(compact('moves','last_seq', 'lastmove'));
  }
  public function listen(){
    $this->load->view("listen");
  }
     public function add($name = "") {
     $success = false;
     if($name)
	  $doc = $this->couch->get("Game");
	 else
	   return;
     if ($_POST) {
       if($doc->move){
         $allMoves = $doc->move;
       }else{
         $allMoves = array();
       }
       $newMove = new stdClass;
       if($name == "City")
         $player = "city";
         else 
        $player = "army";
       $newMove->$player->x = $_POST["x"];
       $newMove->$player->y = $_POST["y"];
       $allMoves[] = $newMove;
       $doc->lastmove->$player = $newMove->$player;
       $doc->move = $allMoves;
       $success = $this->couch->put($doc->_id,$doc);
     }
     return compact('success');
   }
  
	public function put(){
//	  $this->load->driver("couch");
      print_r($this->load->get_package_paths());
	  $doc = $this->couch->get("this is a test.....");
	  //$doc->_id = "this is a test.....";
	  $message = $doc->message ? $doc->message : array();
	  $message[] = "The message is only getting clearer";
	  $doc->message = $message;
	  $this->couch->put($doc->_id,$doc);
	  $this->load->view('put',compact("message"));
	}

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
