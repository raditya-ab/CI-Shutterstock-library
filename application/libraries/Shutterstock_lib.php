<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shutterstock_lib
{
	protected $ci;
	private $accessToken;
	private $clientID;
	private $clientSecret;
	private $subscriptionID;

	public function __construct()
	{
        $this->ci =& get_instance();
		$this->ci->config->load('shutterstock_credential');

        $cred = $this->ci->config->item('shutterstock');

        $this->ci->accessToken = $cred['accessToken'];
        $this->ci->clientID = $cred['clientID'];
        $this->ci->clientSecret = $cred['clientSecret'];
        $this->ci->subscriptionID = $cred['subscriptionID'];
	}

	public function search($search_terms, $type = 'images',$per_page = 5, $view = 'full') {
		$search_terms_for_url = preg_replace('/\s/', '+', $search_terms);
		$url = 'https://api.shutterstock.com/v2/' . $type . '/search?view='.$view.'&per_page='.$per_page.'&query=' . $search_terms_for_url;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->ci->accessToken));
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($ch);
		curl_close($ch);

		header('Content-Type: application/json');
		return json_decode($response);
	}

	public function list_images($id, $view = 'full')
	{
		$url = 'https://api.shutterstock.com/v2/images?id='.$id.'&view='.$view;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->ci->accessToken));
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($ch);
		curl_close($ch);

		header('Content-Type: application/json');
		return json_decode($response);
	}

	public function list_licenses($id = NULL, $per_page = 200){
	  	if(is_null($id)){
		    $url = 'https://api.shutterstock.com/v2/images/licenses?per_page='.$per_page;
	  	} else {
	  		$url = 'https://api.shutterstock.com/v2/images/licenses?per_page='.$per_page.'&image_id='.$id;
	  	}

	    $ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->ci->accessToken));
	    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	    $response = curl_exec($ch);
	    curl_close($ch);

	    header('Content-Type: application/json');
	    return json_decode($response);
	}

	public function list_collections($per_page = 100){
		$url = 'https://api.shutterstock.com/v2/images/collections?per_page='.$per_page;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->ci->accessToken));
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($ch);
		curl_close($ch);

		header('Content-Type: application/json');
		return json_decode($response);
	}

	public function download_image($id)
	{
	    $url = 'https://api.shutterstock.com/v2/images/licenses/'.$id.'/downloads';

	    $params = array(
	      'show_modal'  => true
	    );
	    
	    $ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
	    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer ' . $this->ci->accessToken));
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	    curl_setopt($ch, CURLOPT_VERBOSE, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

	    $response = curl_exec($ch);
	    curl_close($ch);
	    
	    header('Content-Type: application/json');
	    return json_decode($response);
	}

	public function license_image($image_id, $param = NULL){
		$url = 'https://api.shutterstock.com/v2/images/licenses?subscription_id='.$this->ci->subscriptionID;
		
		$params = array();
		$query = array();

		if(count($image_id) > 1){
			foreach($image_id as $i => $id){
				$params['images'][$i] = array(
					'image_id' => $id,
				);
				if($this->is_image_editorial($id)){
					$params['images'][$i]['editorial_acknowledgement'] = true;
				}
			}
		} else {
			$parameter = array();

			$parameter['image_id'] = $image_id;

			if($this->is_image_editorial($image_id)){
				$parameter['editorial_acknowledgement'] = true;
			}

			$params = array();
			$params['images'][] = $parameter;
		}

		if(count($param) > 0){
			
			if(array_key_exists('format', $param)){
				$query['format'] = $param['format'];
			}

			if(array_key_exists('size', $param)){
				$query['size'] = $param['size'];
			}

			$url_query = http_build_query($query);
			$url .= '&'.$url_query;
		}

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer ' . $this->ci->accessToken));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

		$response = curl_exec($ch);
		curl_close($ch);
		
		header('Content-Type: application/json');
		return json_decode($response);
	}

	public function get_user_detail()
	{
		$url = 'https://api.shutterstock.com/v2/user';

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->ci->accessToken));
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($ch);
		curl_close($ch);

		header('Content-Type: application/json');
		return json_decode($response);
	}

	public function list_user_subscription($param = NULL)
	{
		$url = 'https://api.shutterstock.com/v2/user/subscriptions';

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->ci->accessToken));
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($ch);
		curl_close($ch);

		$return_data = json_decode($response);

		if(! is_null($param) && $param == 'current'){
			$subscriptionID = $this->ci->subscriptionID;
			
			$data = json_decode($response);
			for($i = 0; $i < count($data->data); $i++){
				if($data->data[$i]->id == $subscriptionID){
					$return_data =  $data->data[$i];

					return $return_data;
				}
			}
		}

		return $return_data->data;
	}

	public function get_crendential($type)
	{
		switch ($type) {
			case 'accessToken':
				return $this->ci->accessToken;
				break;
			case 'clientID':
				return $this->ci->clientID;
				break;
			case 'clientSecret':
				return $this->ci->clientSecret;
				break;
			case 'subscriptionID':
				return $this->ci->subscriptionID;
				break;
		}
	}
	
	public function is_image_editorial($id){
		$image_data = $this->list_images($id);

		if(property_exists($image_data->data[0], 'is_editorial')){
			return $image_data->data[0]->is_editorial;
		}

		return FALSE;
	}

}

/* End of file shutterstock_lib.php */
/* Location: ./application/libraries/shutterstock_lib.php */
