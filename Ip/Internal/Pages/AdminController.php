<?php
/**
 * @package ImpressPages
 *
 */

namespace Ip\Internal\Pages;





class AdminController extends \Ip\Controller
{


    public function index()
    {
        ipAddJs('Ip/Internal/Ip/assets/js/angular.js');
        ipAddJs('Ip/Internal/Pages/assets/js/pages.js');
        ipAddJs('Ip/Internal/Pages/assets/js/zones.js');
        ipAddJs('Ip/Internal/Pages/assets/js/jquery.pageTree.js');
        ipAddJs('Ip/Internal/Pages/assets/js/jquery.pageProperties.js');
        ipAddJs('Ip/Internal/Pages/assets/jstree/jquery.jstree.js');
        ipAddJs('Ip/Internal/Pages/assets/jstree/jquery.cookie.js');
        ipAddJs('Ip/Internal/Pages/assets/jstree/jquery.hotkeys.js');

        ipAddJsVariable('languageList', Helper::languageList());
        ipAddJsVariable('menuList', Helper::menuList());

        $variables = array(
            'addPageForm' => Helper::addPageForm(),
            'addMenuForm' => Helper::addMenuForm(),
            'languagesUrl' => ipConfig()->baseUrl() . '?aa=Languages.index'
        );
        $layout = ipView('view/layout.php', $variables);
        return $layout->render();
    }

    public function getPages()
    {
        $data = ipRequest()->getRequest();
        if (empty($data['languageId'])) {
            throw new \Ip\Exception("Missing required parameters");
        }
        $languageCode = $data['languageId']; // TODOX use language code
        $languageCode = 'en';

        if (empty($data['menuName'])) {
            throw new \Ip\Exception("Missing required parameters");
        }
        $menuName = $data['menuName'];

        $parentId = ipDb()->selectValue('page', 'id', array('languageCode' => $languageCode, 'alias' => $menuName));
        $responseData = array (
            'tree' => JsTreeHelper::getPageTree($languageCode, $parentId)
        );

        return new \Ip\Response\Json($responseData);

    }

    public function pagePropertiesForm()
    {
        $pageId = ipRequest()->getQuery('pageId');
        if (!$pageId) {
            throw new \Ip\Exception("Missing required parameters");
        }

        $variables = array(
            'form' => Helper::pagePropertiesForm($pageId)
        );
        $layout = ipView('view/pageProperties.php', $variables)->render();

        $data = array (
            'html' => $layout
        );
        return new \Ip\Response\Json($data);
    }

    public function updatePage()
    {
        ipRequest()->mustBePost();
        $data = ipRequest()->getPost();

        if (empty($data['pageId'])) {
            throw new \Ip\Exception("Missing required parameters");
        }
        $pageId = (int)$data['pageId'];

        $answer = array();

        $pageBeforeUpdate = new \Ip\Page($pageId);

        if (strtotime($data['createdAt']) === false) {
            $answer['errors'][] = array('field' => 'createdAt', 'message' => __('Incorrect date format. Example:', 'ipAdmin', false).date(" Y-m-d"));
        }

        if (strtotime($data['lastModified']) === false) {
            $answer['errors'][] = array('field' => 'lastModified', 'message' => __('Incorrect date format. Example:', 'ipAdmin', false).date(" Y-m-d"));
        }

//      TODOXX implement page type in Pages module #138
//        if ($data['type'] == 'redirect' && $data['redirectURL'] == '') {
//            $answer['errors'][] = array('field' => 'redirectURL', 'message' => __('External url can\'t be empty', 'ipAdmin', false));
//        }

        $data['visible'] = !empty($data['visible']);
        if (empty($answer['errors'])) {
            Model::updatePageProperties($pageId, $data);
            $answer['status'] = 'success';
        } else {
            $answer['status'] = 'error';
        }

        if (empty($data['slug'])) {

            Model::regeneratePageSlug($pageId);

        } elseif ($data['slug'] != $pageBeforeUpdate->getSlug()) {

            Model::updatePageSlug($pageId, $data['slug']);
        }

        return new \Ip\Response\Json($answer);
    }

    public function updateMenuForm()
    {
        $menuId = ipRequest()->getQuery('id');
        if (empty($menuId)) {
            throw new \Ip\Exception("Missing required parameters");
        }

        $form = Helper::menuForm($menuId);
        $html = $form->render();

        $data = array (
            'html' => $html
        );
        return new \Ip\Response\Json($data);
    }

    public function updateMenu()
    {
        $request = ipRequest();

        $menuId = $request->getPost('id');
        $title = $request->getPost('title');
        $alias = $request->getPost('alias');
        $layout = $request->getPost('layout');

        if (empty($menuId) || empty($title) || empty($alias) || empty($layout)) {
            throw new \Ip\Exception('Missing required parameters');
        }

        Service::updateMenu($menuId, $alias, $title, $layout);

        $answer = array(
            'status' => 'success'
        );

        return new \Ip\Response\Json($answer);
    }

    public function deleteZone()
    {
        ipRequest()->mustBePost();
        $data = ipRequest()->getPost();

        if (empty($data['zoneName'])) {
            throw new \Ip\Exception('Missing required parameters');
        }
        $zoneName = $data['zoneName'];

        Service::deleteZone($zoneName);

        $answer = array(
            'status' => 'error'
        );

        return new \Ip\Response\Json($answer);
    }

    public function createMenu()
    {
        $request = ipRequest();
        $request->mustBePost();
        $languageCode = $request->getPost('languageCode');
        $title = $request->getPost('title');

        if (empty($title)) {
            $title = __('Untitled', 'ipAdmin', false);
        }

        $transliterated = \Ip\Internal\Text\Transliteration::transform($title);
        $alias = preg_replace('/[^a-z0-9_\-]/i', '', strtolower($transliterated));

        $menuAlias = Service::createMenu($languageCode, $alias, $title);

        $answer = array(
            'status' => 'success',
            'menuName' => $menuAlias
        );

        return new \Ip\Response\Json($answer);
    }

    public function addPage()
    {
        ipRequest()->mustBePost();

        $parentId = ipRequest()->getPost('parentId');
        if (empty($parentId)) {
            throw new \Ip\Exception("Missing required parameters");
        }

        $title = ipRequest()->getPost('title');
        if (empty($title)) {
            $title = __('Untitled', 'ipAdmin', false);
        }

        $visible = ipRequest()->getPost('visible', 0);

        $pageId = Service::addPage($parentId, $title, array('visible' => $visible));


        $answer = array(
            'status' => 'success',
            'pageId' => $pageId
        );

        return new \Ip\Response\Json($answer);

    }

    public function deletePage()
    {
        ipRequest()->mustBePost();

        $pageId = (int)ipRequest()->getPost('pageId');
        if (!$pageId) {
            throw new \Ip\Exception("Page id is not set");
        }

        Service::deletePage($pageId);

        $answer = array ();
        $answer['status'] = 'success';

        return new \Ip\Response\Json($answer);
    }

    public function movePage()
    {
        ipRequest()->mustBePost();
        $data = ipRequest()->getPost();


        if (!isset($data['pageId'])) {
            throw new \Ip\Exception("Page id is not set");
        }
        $pageId = (int)$data['pageId'];


        if (!empty($data['destinationParentId'])) {
            $destinationParentId = $data['destinationParentId'];
        } else {
            if (!isset($data['languageId'])) {
                throw new \Ip\Exception("Missing required parameters");
            }
            throw new \Ip\Exception\NotImplemented();
        }


        if (!isset($data['destinationPosition'])) {
            throw new \Ip\Exception("Destination position is not set");
        }
        $destinationPosition = $data['destinationPosition'];


        try {
            Model::movePage($pageId, $destinationParentId, $destinationPosition);
        } catch (\Ip\Exception $e) {
            $answer = array (
                'status' => 'error',
                'error' => $e->getMessage()
            );
            return new \Ip\Response\Json($answer);
        }


        $answer = array (
            'status' => 'success'
        );

        return new \Ip\Response\Json($answer);



    }


    public function copyPage()
    {
            ipRequest()->mustBePost();
            $data = ipRequest()->getPost();


            if (!isset($data['pageId'])) {
                throw new \Ip\Exception("Page id is not set");
            }
            $pageId = (int)$data['pageId'];


            if (!empty($data['destinationParentId'])) {
                $destinationParentId = $data['destinationParentId'];
            } else {
                if (!isset($data['zoneName'])) {
                    throw new \Ip\Exception("Missing required parameters");
                }
                if (!isset($data['languageId'])) {
                    throw new \Ip\Exception("Missing required parameters");
                }
                $zone = ipContent()->getZone($data['zoneName']);
                $destinationParentId = Db::rootId($zone->getId(), $data['languageId']);
            }


            if (!isset($data['destinationPosition'])) {
                throw new \Ip\Exception("Destination position is not set");
            }
            $destinationPosition = $data['destinationPosition'];


            try {
                Service::copyPage($pageId, $destinationParentId, $destinationPosition);
            } catch (\Ip\Exception $e) {
                $answer = array (
                    'status' => 'error',
                    'error' => $e->getMessage()
                );
                return new \Ip\Response\Json($answer);
            }


            $answer = array (
                'status' => 'success'
            );

            return new \Ip\Response\Json($answer);


    }

    public function getPageUrl()
    {
        $data = ipRequest()->getQuery();


        if (!isset($data['pageId'])) {
            throw new \Ip\Exception("Page id is not set");
        }
        $pageId = (int)$data['pageId'];

        $page = new \Ip\Page($pageId);

        $answer = array (
            'pageUrl' => $page->getLink()
        );

        return new \Ip\Response\Json($answer);
    }

    public function sortZone()
    {
        ipRequest()->mustBePost();
        $data = ipRequest()->getPost();


        if (empty($data['zoneName']) || !isset($data['newIndex'])) {
            throw new \Ip\Exception("Missing required parameters");
        }
        $zoneName = $data['zoneName'];
        $newIndex = $data['newIndex'];

        Model::sortZone($zoneName, $newIndex);

        return new \Ip\Response\Json(array(
            'error' => 0
        ));
    }


}
