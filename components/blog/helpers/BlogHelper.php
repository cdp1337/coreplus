<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/29/12
 * Time: 10:13 PM
 * To change this template use File | Settings | File Templates.
 */
abstract class BlogHelper {
	/**
	 * Helper function to save blog pages, both new and existing.
	 *
	 * @static
	 *
	 * @param Form $form
	 */
	public static function BlogFormHandler(Form $form) {
		$model = $form->getModel();
		$page  = $model->getLink('Page');

		foreach ($form->getElements() as $el) {
			$n = $el->get('name');

			if (strpos($n, 'page[') === 0) {
				$page->set(substr($n, 5, -1), $el->get('value'));
			}
		}
		$page->set('fuzzy', '1'); // Needs to be fuzzy since it supports children
		$model->save();
		return $model->get('baseurl');
	}

	/**
	 * Helper function to save a blog article, both new and existing.
	 *
	 * @static
	 *
	 * @param Form $form
	 */
	public static function BlogArticleFormHandler(Form $form) {
		try{
			/** @var $article BlogArticleModel */
			$article = $form->getModel();
			$isnew = !$article->exists();
			if(!$article->exists()){
				$article->set('authorid', \Core\user()->get('id'));
			}
			$article->save();

			// if it's new, allow the user to post it to facebook.
			if($isnew && isset($_POST['facebook_post']) && $_POST['facebook_post']){
				// facebook_post

				$token = substr($_POST['facebook_post'], strpos($_POST['facebook_post'], ':')+1);
				$fbid  = substr($_POST['facebook_post'], 0, strpos($_POST['facebook_post'], ':'));
				$from  = \Core\user()->get('facebook_id');

				// yay....
				$args = array(
					'access_token' => $token,
					'from' => $from,
					'link' => Core::ResolveLink($article->get('rewriteurl')),
					'name' => $article->get('title'),
					'caption' => '',
					'description' => $article->getTeaser(),
					'message' => '',
				);

				// Some optional arguments
				if($article->getImage()){
					$args['picture'] = $article->getImage()->getPreviewURL('300x300');
				}
				$args['ref'] = 'coreplus';

				$facebook = new Facebook(array(
					'appId'  => FACEBOOK_APP_ID,
					'secret' => FACEBOOK_APP_SECRET,
				));
				$publish_result = $facebook->api('/' . $fbid . '/feed', 'POST', $args);
				$article->set('fb_account_id', $fbid);
				$article->set('fb_post_id', $publish_result['id']);
				$article->save();
			}

			return $article->get('rewriteurl');
		}
		catch(ModelValidationException $e){
			Core::SetMessage($e->getMessage(), 'error');
			return false;
		}
		catch(FacebookApiException $e){
			// Facebook errors are not critical, as the post is still created.
			Core::SetMessage($e->getMessage(), 'error');
			return $article->get('rewriteurl');
		}
		catch(Exception $e){
			error_log($e->getMessage());
			Core::SetMessage($e->getMessage(), 'error');
			return false;
		}
	}
}
