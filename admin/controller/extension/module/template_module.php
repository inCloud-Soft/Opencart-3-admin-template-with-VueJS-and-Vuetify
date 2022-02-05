<?php
class ControllerExtensionModuleTemplateModule extends Controller {

	public function __construct($registry) {
		parent::__construct($registry);
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && !count($this->request->post)){
			$this->request->post = json_decode(file_get_contents("php://input"),true);
		}
	}

	public function index() {
		$this->load->language('extension/module/template_module');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/template_module', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['user_token'] = $this->session->data['user_token'];
		$data['template_module_status'] = $this->config->get('template_module_status');
		$data['template_module'] = $this->config->get('template_module');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['action'] = $this->url->link('extension/module/template_module/update_status', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], true);

		$data['get_all_categories_url'] = str_replace('&amp;', '&', $this->url->link('extension/module/template_module/all_categories', 'user_token=' . $this->session->data['user_token'], true));
		$data['update_status_url'] = str_replace('&amp;', '&', $this->url->link('extension/module/template_module/update_status', 'user_token=' . $this->session->data['user_token'], true));

		$this->response->setOutput($this->load->view('extension/module/template_module', $data));
	}
	
	function update_status(){
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['status'])){
			$this->load->model('setting/setting');
			$status = $this->request->post["status"];
			$settings = $this->model_setting_setting->getSetting('template_module');
			$settings["template_module_status"] = $status;
			$this->model_setting_setting->editSetting('template_module', $settings);
		}
		$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true));
	}

	protected function responseJSON($data, $sort_key = ''){
		if ($sort_key){
			$sort_order = array();

			foreach ($data as $key => $value) {
				$sort_order[$key] = $value[$sort_key];
			}

			array_multisort($sort_order, SORT_ASC, $data);
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}

	function install(){
	}

	public function getAllCategoriesCached() {
		$result = $this->cache->get('category.all.' . $this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'));

		if (!$result || !is_array($result)) {
			$this->load->model('catalog/category');

			$result = $this->model_catalog_category->getCategories();
		
			$this->cache->set('category.all.' . $this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'), $result);
		}

		return $result;
	}

	public function all_categories() {
		$json = array();

		$results = $this->getAllCategoriesCached();

		foreach ($results as $result) {
			$json[] = array(
				'category_id' => $result['category_id'],
				'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
			);
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}