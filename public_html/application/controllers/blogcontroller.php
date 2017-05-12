<?php
class BlogController extends CommonController {
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        Syspage::addCss(array('css/blog.css'));
        Syspage::addCss(array('css/ionicons.css'), true);
        $userObj=new User();
        $this->user_details=$userObj->getUserById($this->getLoggedUserId());  
    }
    function default_action() {
        $this->right_panel();
        $this->_template->render();
    }
    function getUserSession($key) {
        $nameFieldArr=array('user_first_name','user_last_name');
        if(in_array($key,$nameFieldArr)){
            $name=$this->user_details['user_name'];
            $nameArr=explode(' ',$name);
            list($user_first_name,$user_last_name)=$nameArr;
            if($key=='user_first_name'){return $user_first_name;}
            if($key=='user_last_name'){return $user_last_name;}
        }       
        return $this->user_details[$key];
    }
    function category($cat_slug) {
        if (empty($cat_slug)) {
            Message::addErrorMessage(Utilities::getLabel('M_INVALID_REQUEST'));
            Utilities::redirectUser(Utilities::generateUrl('blog'));
        }
        $frm = $this->getCategoryForm();
        $frm->getField('cat_slug')->value = $cat_slug;
        $meta_data_record = $this->Blog->getCategoryMetaDataByCatSlug($cat_slug);
        $meta_data = array(
            'meta_title' => $meta_data_record['meta_title'],
            'meta_description' => $meta_data_record['meta_description'],
            'meta_keywords' => $meta_data_record['meta_keywords'],
            'meta_others' => $meta_data_record['meta_others']
            );
        $this->set('meta_data', $meta_data);
        $this->set('frmCategory', $frm);
        $this->right_panel();
        $this->_template->render();
    }
    function archives($year, $month) {
        if ($year < 1 || $month < 1) {
            Message::addErrorMessage(Utilities::getLabel('M_INVALID_REQUEST'));
            Utilities::redirectUser(Utilities::generateUrl('blog'));
        }
        $frm = $this->getArchivesForm();
        $frm->getField('year')->value = $year;
        $frm->getField('month')->value = $month;
        $this->set('frmArchives', $frm);
        $this->right_panel();
        $this->_template->render();
    }
    function listArchivesPost() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0)
                $page = intval($post['page']);
            else
                $post['page'] = $page;
            $pagesize = FRONTPAGESIZE;
            $post['pagesize'] = $pagesize;
            $post_data = $this->Blog->getArchivesPost($post);
            $posts = array();
            if ($post_data) {
                foreach ($post_data as $pd) {
                    /* get post categories[ */
                    $pd['post_categories'] = array();
                    $pd['post_categories'] = $this->Blog->getPostCategories(array('post_id' => $pd['post_id']));
                    /* ] */
                    $posts[] = $pd;
                }
            }
            $this->set('records', $posts);
            $this->set('pages', $this->Blog->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Blog->getTotalRecords();
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->right_panel();
            $this->_template->render(false, false, 'blog/listPost.php');
        }
        die(0);
    }
    function listCatPost() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0)
                $page = intval($post['page']);
            else
                $post['page'] = $page;
            $pagesize = FRONTPAGESIZE;
            $post['pagesize'] = $pagesize;
            $posts_data = $this->Blog->getCatPosts($post);
            $posts = array();
            if ($posts_data) {
                foreach ($posts_data as $pd) {
                    /* get post categories[ */
                    $pd['post_categories'] = array();
                    $pd['post_categories'] = $this->Blog->getPostCategories(array('post_id' => $pd['post_id']));
                    /* ] */
                    $posts[] = $pd;
                }
            }
            $this->set('records', $posts);
            $this->set('pages', $this->Blog->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Blog->getTotalRecords();
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->right_panel();
            $this->_template->render(false, false, 'blog/listPost.php');
        }
        die(0);
    }
    private function right_panel() {
        $pagesize = 4;
        $post['pagesize'] = $pagesize;
        $this->set('recent_post', $this->Blog->getRecentPost($post));
        $all_categories = $this->Blog->getAllCategories();
        if(!empty($all_categories)){
            $sort_categories = $this->sortCategories($all_categories);
        }
        $this->set('categories', $sort_categories);
        $archives = $this->Blog->getArchives();
        $this->set('archives', $archives);
        $frm = $this->getSearchForm();
        $frm->setRequiredStarPosition('0');
        $this->set('frmSearchForm', $frm);
    }
    private function getSearchForm() {
        $frm = new Form('frmSearchPost', 'frmSearchPost');
        $frm->setAction(Utilities::generateUrl('blog', 'search'));
        $frm->setExtra('class="search-form"');
        $frm->setJsErrorDisplay('afterfield');
        $frm->addHiddenField('', 'page', 1);
        $fld = $frm->addRequiredField('', 'search', '', 'search', 'title="'.Utilities::getLabel('L_Search_Text').'" placeholder="'.Utilities::getLabel('L_Search').'"');
        $fld->setRequiredStarWith('none');
        $frm->addSubmitButton('', 'btn_submit', 'Submit', 'btn_submit', ' class="button" ');
        return $frm;
    }
    public function search() {
        $this->right_panel();
        $post = Syspage::getPostedVar();
        $frm = $this->getSearchForm();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])) {
            if (!$frm->validate($post)) {
                Message::addErrorMessage($frm->getValidationErrors());
                Utilities::redirectUser(Utilities::generateUrl('blog'));
            } else {
                $this->set('searchString', $post['search']);
                $frm->getfield('search')->value = $post['search'];
            }
            $this->set('frmSearchForm', $frm);
        }
        $this->_template->render();
    }
    public function searchlist() {
        $post = Syspage::getPostedVar();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->set('searchString', $post['search']);
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0)
                $page = intval($post['page']);
            else
                $post['page'] = $page;
            $pagesize = FRONTPAGESIZE;
            $post['pagesize'] = $pagesize;
            $posts = array();
            if ($records = $this->Blog->getSearchPost($post)) {
                if ($records) {
                    foreach ($records as $record) {
                        /* get post categories[ */
                        $record['post_categories'] = array();
                        $record['post_categories'] = $this->Blog->getPostCategories(array('post_id' => $record['post_id']));
                        /* ] */
                        $posts[] = $record;
                    }
                }
                $this->set('records', $posts);
                $this->set('pages', $this->Blog->getTotalPages());
                $this->set('page', $page);
            }
        }
        $this->_template->render(false, false, 'blog/listPost.php');
    }
    function contribution() {
        Syspage::addCss(array('css/ionicons.css'), true);
        $frm = $this->getContributeForm();
        if ($this->isUserLogged()) {
            $frm->getField('contribution_author_first_name')->value = $this->getUserSession('user_first_name');
            $frm->getField('contribution_author_last_name')->value = $this->getUserSession('user_last_name');
            $frm->getField('contribution_author_email')->value = $this->getUserSession('user_email');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if( !$post = Syspage::getPostedVar()){
                Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST')); 
                Utilities::redirectUser(Utilities::generateUrl('blog', 'contribution'));
            }
            if (!Utilities::verifyCaptcha()) {
                unset($_FILES['contribution_file_name']);
                $frm->fill($post); 
                Message::addErrorMessage(Utilities::getLabel('M_ERROR_PLEASE_VERIFY_YOURSELF'));
            } else {
                if (isset($_FILES['contribution_file_name']) && $_FILES['contribution_file_name']['error'] == 0) {
                    $post['contribution_file_name'] = $_FILES['contribution_file_name']['name'];
                }
                if (!$frm->validate($post)) {
                    unset($_FILES['contribution_file_name']);
                    $frm->fill($post);
                    Message::addErrorMessage($frm->getValidationErrors()); 
                } else {
                    if (isset($post['contribution_file_name'])) {
                        unset($post['contribution_file_name']);
                    }
                    $error = 0;
                    if (!empty($_FILES['contribution_file_name']['name'])) {
                        if ($_FILES['contribution_file_name']['size'] > (1024 * 1024 * CONF_CONTRIBUTION_FILE_UPLOAD_SIZE)) {
                            $frm->fill($post);
                            $labelMsg = str_replace('{VAR}', CONF_CONTRIBUTION_FILE_UPLOAD_SIZE, Utilities::getLabel('M_ERROR_FILE_SIZE_SHOULD_NOT_EXCEED_{VAR}_MB'));
                            Message::addErrorMessage($labelMsg);
                            $error = 1;
                        } else {
                            if (is_uploaded_file($_FILES['contribution_file_name']['tmp_name'])) {
                                $saved_image_name = '';
                                if (!($this->uploadContributionFile($_FILES['contribution_file_name']['tmp_name'], $_FILES['contribution_file_name']['name'], $saved_image_name, 'contributions/'))) {
                                    Message::addErrorMessage($saved_image_name); 
                                    unset($_FILES['contribution_file_name']);
                                    $frm->fill($post);
                                    $error = 1;
                                } else {
                                    $post['contribution_file_display_name'] = $_FILES['contribution_file_name']['name'];
                                    $post['contribution_file_name'] = $saved_image_name;
                                }
                            }
                        }
                    } 
                    if ($error == 0) {
                        if ($this->Blog->addContributions($post)) {
                            $emailnotifications = new Emailnotifications();
                            $emailnotifications->sendContributionEmailToAdmin($post);
                            Message::addMessage(Utilities::getLabel('M_SUCCESS_DETAILS_SAVED'));                             
                        } else {
                            Message::addErrorMessage(Utilities::getLabel('M_ERROR_DETAILS_NOT_SAVED'));
                        }
                    }
                }
            }
//Utilities::redirectUser(Utilities::generateUrl('blog', 'contribution'));
        }
        $this->set('frmContribute', $frm);
        $frm->setRequiredStarPosition('0');
        $this->_template->render();
    }
    function uploadContributionFile($file_tmp_name, $filename, &$response, $pathSuffix = '') {
        $finfo = finfo_open(FILEINFO_MIME_TYPE); 
        $file_mime_type = finfo_file($finfo, $file_tmp_name);
        $accepted_files = array(
            'application/pdf',
            'application/octet-stream',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword',
            'text/plain',
            'application/zip',
            'application/x-rar'
            );
        if (!in_array(trim($file_mime_type), $accepted_files)) {
            $response = Utilities::getLabel('M_ERROR_VALID_FILES_FOR_BLOG_CONTRIBUTION');
            return false;
        }
        $fname = preg_replace('/[^a-zA-Z0-9]/', '', $filename);
        while (file_exists(CONF_INSTALLATION_PATH . 'user-uploads/' . $pathSuffix . $fname)) {
            $fname .= '_' . rand(10, 999999);
        }
        if (!copy($file_tmp_name, CONF_INSTALLATION_PATH . 'user-uploads/' . $pathSuffix . $fname)) {
            $response = Utilities::getLabel('M_ERROR_COULD_NOT_SAVE_FILE');
            return false;
        }
        $response = $fname;
        return true;
    }
    private function getContributeForm() {
        $frm = new Form('frmContribute', 'frmContribute');
        $frm->setJsErrorDisplay('afterfield');
        $frm->addRequiredField('', 'contribution_author_first_name', '', 'contribution_author_first_name', 'title="'.Utilities::getLabel('L_First_Name').'" placeholder="'.Utilities::getLabel('L_First_Name*').'"');
        $frm->addRequiredField('', 'contribution_author_last_name', '', 'contribution_author_last_name', 'title="'.Utilities::getLabel('L_Last_Name').'" placeholder="'.Utilities::getLabel('L_Last_Name*').'"');
        $frm->addEmailField('', 'contribution_author_email', '', 'contribution_author_email', 'title="'.Utilities::getLabel('L_Email_Address').'" placeholder="'.Utilities::getLabel('L_Email_Address*').'"');
        $fld = $frm->addTextBox('', 'contribution_author_phone', '', 'contribution_author_phone', 'title="'.Utilities::getLabel('L_Phone_No').'" placeholder="'.Utilities::getLabel('L_Phone_No.').'"');
        $fld->requirements()->setInt();
        $fld = $frm->addFileUpload('Upload File', 'contribution_file_name', 'contribution_file_name', 'title="'.Utilities::getLabel('L_Upload_File').'"');
        $fld->requirements()->setRequired();
		if (!empty(CONF_RECAPTACHA_SITEKEY)){
        	$frm->addHtml('', 'captcha_code','<div class="g-recaptcha" data-sitekey="'.CONF_RECAPTACHA_SITEKEY.'"></div>');
		}
        $frm->addHiddenField('', 'contribution_user_id', $this->getLoggedUserId(), 'contribution_user_id');
        $frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('L_Submit'), 'btn_submit', 'class="themeBtn"');
        return $frm;
    }
    private function getCategoryForm() {
        $frm = new Form('frmCategory', 'frmCategory');
        $frm->addHiddenField('', 'page', 1);
        $frm->addHiddenField('', 'cat_slug', '', 'cat_slug');
        return $frm;
    }
    private function getArchivesForm() {
        $frm = new Form('frmArchives', 'frmArchives');
        $frm->addHiddenField('', 'page', 1);
        $frm->addHiddenField('', 'year', '', 'year');
        $frm->addHiddenField('', 'month', '', 'month');
        return $frm;
    }
    private function getCommentForm() {
        $frm = new Form('frmComment', 'frmComment');
        $frm->setExtra('class="siteForm"');
        $frm->setTableProperties('class="reviewTbl"');
        $frm->setFieldsPerRow(2);
        $frm->setJsErrorDisplay('afterfield');
        $fld = $frm->addRequiredField('', 'comment_author_name', '', 'comment_author_name', 'title="'.Utilities::getLabel('L_Name').'" placeholder="'.Utilities::getLabel('L_Name*').'"');
        $fld->setRequiredStarWith('none');
//$fld->setRequiredStarPosition('before');
        $fld = $frm->addEmailField('', 'comment_author_email', '', 'comment_author_email', 'title="'.Utilities::getLabel('L_Email').'" placeholder="'.Utilities::getLabel('L_Email*').'"');
        $fld->setRequiredStarWith('none');
		if (!empty(CONF_RECAPTACHA_SITEKEY)){
        	$frm->addHtml('', 'captcha_code','<div class="g-recaptcha" data-sitekey="'.CONF_RECAPTACHA_SITEKEY.'"></div>');
		}
        $fld = $frm->addTextArea('', 'comment_content', '', 'comment_content', 'class="textarea_resize" rows="8" placeholder="'.Utilities::getLabel('L_Comment').'" title="'.Utilities::getLabel('L_Comment*').'"');
        $fld->requirements()->setRequired(true);
        $fld->setRequiredStarWith('none'); 
        $frm->addHiddenField('', 'comment_post_id', '', 'comment_post_id');
        $frm->addHiddenField('', 'comment_user_id', $this->getLoggedUserId(), 'comment_user_id');
        $frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('L_Submit'), 'btn_submit', 'class="themeBtn"');
        return $frm;
    }
    function listPost() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0)
                $page = intval($post['page']);
            else
                $post['page'] = $page;
            $pagesize = FRONTPAGESIZE;
            $post['pagesize'] = $pagesize;
            $post_data = $this->Blog->getBlogPosts($post);
            $posts = array();
            if ($post_data) {
                foreach ($post_data as $pd) {
                    /* get post categories[ */
                    $pd['post_categories'] = array();
                    $pd['post_categories'] = $this->Blog->getPostCategories(array('post_id' => $pd['post_id']));
                    /* ] */
                    $posts[] = $pd;
                }
            }
            $this->set('records', $posts);
            $this->set('pages', $this->Blog->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Blog->getTotalRecords();
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
        die(0);
    }
    private function sortCategories(array $elements, $parentId = 0) {
        $branch = array();
        foreach ($elements as $element) {
            if ($element['category_parent'] == $parentId) {
                $children = $this->sortCategories($elements, $element['category_id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }
    function post($post_slug) {
//Syspage::addCss(array('css/ionicons.css'), true);
        if (empty($post_slug)) {
            Message::addErrorMessage('Invalid request !!');
            Utilities::redirectUser(Utilities::generateUrl('blog'));
        }
        $frm = $this->getCommentForm();
        if ($this->getLoggedUserId()) {
            $frm->removeField($frm->getField('comment_author_name'));
            $frm->removeField($frm->getField('comment_author_email'));
        }
        $post = Syspage::getPostedVar();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])) {
            if (!Utilities::verifyCaptcha()) { 
                $frm->fill($post);
                Message::addErrorMessage(Utilities::getLabel('M_ERROR_PLEASE_VERIFY_YOURSELF'));
            } else {
                if (!$frm->validate($post)) { 
                    $frm->fill($post);
                    Message::addErrorMessage($frm->getValidationErrors());
                } else {
                    if ($this->getLoggedUserId()) {
                        $post['comment_author_name'] = $this->getUserSession('user_first_name') . ' ' . $this->getUserSession('user_last_name');
                        $post['comment_author_email'] = $this->getUserSession('user_email');
                    }
                    if ($this->Blog->addComment($post)) {
                        $emailnotifications = new Emailnotifications();
                        $emailnotifications->sendblogCommentEmail($post);
                        Message::addMessage(Utilities::getLabel('M_SUCCESS_COMMENT_SAVED'));
                        Utilities::redirectUser(Utilities::generateUrl('blog', 'post', array($post_slug)));
                    } else {
                        Message::addErrorMessage(Utilities::getLabel('M_ERROR_COMMENT_NOT_SAVED'));
                    }
                }
            }
        }
        $this->right_panel();
        $post_data = $this->Blog->getPost($post_slug);      
        $post_comment_count = $this->Blog->getPostCommentCount($post_data['post_id']);
        $this->set('comment_count', $post_comment_count[0]['comment_count']);
        $meta_data = array(
            'meta_title' => $post_data['meta_title'],
            'meta_description' => $post_data['meta_description'],
            'meta_keywords' => $post_data['meta_keywords'],
            'meta_others' => $post_data['meta_others']
            );
        $this->set('meta_data', $meta_data);
        if ($post_data['post_id'] != '') {
            $frm->getField('comment_post_id')->value = $post_data['post_id'];
            $postid = $post_data['post_id'];
            if (empty($_SESSION['postid'])) {
                $_SESSION['postid'] = $postid;
                $flag = 1;
            } else {
                $finalarray = explode(',', $_SESSION['postid']);
                if (in_array($postid, $finalarray)) {
                    $flag = 0;
                } else {
                    $_SESSION['postid'] .= ',' . $postid;
                    $flag = 1;
                }
            }
            if ($flag == 1) {
                $this->Blog->setPostViewsCount($post_data['post_id']);
            }
            Syspage::addJs(array('js/slick.min.js'), true);
            Syspage::addCss(array('css/slick.css'), true);
            /* get post categories[ */
            $post_data['post_categories'] = array();
            $post_data['post_categories'] = $this->Blog->getPostCategories(array('post_id' => $post_data['post_id']));
            /* ] */
            $this->set('post_data', $post_data);
            $post_slider_images = $this->Blog->getPostImages($post_slug);
            if (!empty($post_slider_images)) {
                $this->set('slider_images', $post_slider_images);
            }
        }
		if($post_data){	
			$post_description = trim(subStringByWords(strip_tags(Utilities::renderHtml($post_data["post_content"],true)),500));
			$post_description .= ' - '.Utilities::getLabel('L_See_more_at').": ".Utilities::getCurrUrl();	$postImageUrl = '';
			if (!empty($post_slider_images[0]['slide_images'])) {
					$postImageUrl = Utilities::generateUrl('image', 'post', array('large', $post_slider_images[0]['slide_images']));
			}
			$socialShareContent = array(
				'type'=>'article',
				'title'=>$post_data['post_title'],
				'description'=>preg_replace("/\s\s+/", " ", $post_description),
				'image'=>$postImageUrl,
			);
			$this->set( 'socialShareContent', $socialShareContent);
		}
        $this->set('loggedUserId', $this->getLoggedUserId());
        $this->set('frmComment', $frm);
		$this->set('blogMetaData', $meta_data);
        $this->_template->render(true,true,'',false,true);
    }
    function listcommentsofpost() {
        $post = Syspage::getPostedVar();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($post['post_id'] < 1) {
                die(0);
            }
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0)
                $page = intval($post['page']);
            else
                $post['page'] = $page;
            $pagesize = FRONTPAGESIZE;
            $post['pagesize'] = $pagesize;
            $post['post_id'] = $post['post_id'];
            $this->set('records', $this->Blog->getPostComments($post));
            $this->set('pages', $this->Blog->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Blog->getTotalRecords();
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false,'blog/listCommentsOfPost.php');
        }
        die(0);
    }
}
