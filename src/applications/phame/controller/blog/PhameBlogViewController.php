<?php

final class PhameBlogViewController extends PhameLiveController {

  public function handleRequest(AphrontRequest $request) {
    $response = $this->setupLiveEnvironment();
    if ($response) {
      return $response;
    }

    $viewer = $this->getViewer();
    $blog = $this->getBlog();

    $is_live = $this->getIsLive();
    $is_external = $this->getIsExternal();

    $pager = id(new AphrontCursorPagerView())
      ->readFromRequest($request);

    $post_query = id(new PhamePostQuery())
      ->setViewer($viewer)
      ->withBlogPHIDs(array($blog->getPHID()));

    if ($is_live) {
      $post_query->withVisibility(PhameConstants::VISIBILITY_PUBLISHED);
    }

    $posts = $post_query->executeWithCursorPager($pager);

    $header = id(new PHUIHeaderView())
      ->setHeader($blog->getName())
      ->setUser($viewer);

    if (!$is_external) {
      if ($blog->isArchived()) {
        $header_icon = 'fa-ban';
        $header_name = pht('Archived');
        $header_color = 'dark';
      } else {
        $header_icon = 'fa-check';
        $header_name = pht('Active');
        $header_color = 'bluegrey';
      }
      $header->setStatus($header_icon, $header_color, $header_name);

      $actions = $this->renderActions($blog);
      $action_button = id(new PHUIButtonView())
        ->setTag('a')
        ->setText(pht('Actions'))
        ->setHref('#')
        ->setIconFont('fa-bars')
        ->addClass('phui-mobile-menu')
        ->setDropdownMenu($actions);

      $header->addActionLink($action_button);

      $header->setPolicyObject($blog);
    }

    $post_list = id(new PhamePostListView())
      ->setPosts($posts)
      ->setViewer($viewer)
      ->setIsExternal($is_external)
      ->setIsLive($is_live)
      ->setNodata(pht('This blog has no visible posts.'));

    $page = id(new PHUIDocumentViewPro())
      ->setHeader($header)
      ->appendChild($post_list);

    $description = null;
    if (strlen($blog->getDescription())) {
      $description = new PHUIRemarkupView(
        $viewer,
        $blog->getDescription());
    } else {
      $description = phutil_tag('em', array(), pht('No description.'));
    }

    $about = id(new PhameDescriptionView())
      ->setTitle(pht('About %s', $blog->getName()))
      ->setDescription($description)
      ->setImage($blog->getProfileImageURI());

    $crumbs = $this->buildApplicationCrumbs();

    $page = $this->newPage()
      ->setTitle($blog->getName())
      ->setPageObjectPHIDs(array($blog->getPHID()))
      ->setCrumbs($crumbs)
      ->appendChild(
        array(
          $page,
          $about,
      ));

    if ($is_live) {
      $page
        ->setShowChrome(false)
        ->setShowFooter(false);
    }

    return $page;
  }

  private function renderActions(PhameBlog $blog) {
    $viewer = $this->getViewer();

    $actions = id(new PhabricatorActionListView())
      ->setObject($blog)
      ->setObjectURI($this->getRequest()->getRequestURI())
      ->setUser($viewer);

    $can_edit = PhabricatorPolicyFilter::hasCapability(
      $viewer,
      $blog,
      PhabricatorPolicyCapability::CAN_EDIT);

    $actions->addAction(
      id(new PhabricatorActionView())
        ->setIcon('fa-plus')
        ->setHref($this->getApplicationURI('post/edit/?blog='.$blog->getID()))
        ->setName(pht('Write Post'))
        ->setDisabled(!$can_edit)
        ->setWorkflow(!$can_edit));

    $actions->addAction(
      id(new PhabricatorActionView())
        ->setUser($viewer)
        ->setIcon('fa-globe')
        ->setHref($blog->getLiveURI())
        ->setName(pht('View Live')));

    $actions->addAction(
      id(new PhabricatorActionView())
        ->setIcon('fa-pencil')
        ->setHref($this->getApplicationURI('blog/manage/'.$blog->getID().'/'))
        ->setName(pht('Manage Blog')));

    return $actions;
  }

}