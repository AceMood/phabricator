<?php

final class PhabricatorPasteEditEngine
  extends PhabricatorEditEngine {

  const ENGINECONST = 'paste.paste';

  public function getEngineName() {
    return pht('Pastes');
  }

  public function getEngineApplicationClass() {
    return 'PhabricatorPasteApplication';
  }

  protected function newEditableObject() {
    return PhabricatorPaste::initializeNewPaste($this->getViewer());
  }

  protected function newObjectQuery() {
    return id(new PhabricatorPasteQuery())
      ->needRawContent(true);
  }

  protected function getObjectCreateTitleText($object) {
    return pht('Create New Paste');
  }

  protected function getObjectEditTitleText($object) {
    return pht('Edit %s %s', $object->getMonogram(), $object->getTitle());
  }

  protected function getObjectEditShortText($object) {
    return $object->getMonogram();
  }

  protected function getObjectCreateShortText() {
    return pht('Create Paste');
  }

  protected function getCommentViewHeaderText($object) {
    return pht('Eat Paste');
  }

  protected function getCommentViewButtonText($object) {
    return pht('Nom Nom Nom Nom Nom');
  }

  protected function getObjectViewURI($object) {
    return '/P'.$object->getID();
  }

  protected function buildCustomEditFields($object) {
    $langs = array(
      '' => pht('(Detect From Filename in Title)'),
    ) + PhabricatorEnv::getEnvConfig('pygments.dropdown-choices');

    return array(
      id(new PhabricatorTextEditField())
        ->setKey('title')
        ->setLabel(pht('Title'))
        ->setDescription(pht('Name of the paste.'))
        ->setTransactionType(PhabricatorPasteTransaction::TYPE_TITLE)
        ->setValue($object->getTitle()),
      id(new PhabricatorSelectEditField())
        ->setKey('language')
        ->setLabel(pht('Language'))
        ->setDescription(
          pht(
            'Programming language to interpret the paste as for syntax '.
            'highlighting. By default, the language is inferred from the '.
            'title.'))
        ->setAliases(array('lang'))
        ->setTransactionType(PhabricatorPasteTransaction::TYPE_LANGUAGE)
        ->setIsCopyable(true)
        ->setValue($object->getLanguage())
        ->setOptions($langs),
      id(new PhabricatorTextAreaEditField())
        ->setKey('text')
        ->setLabel(pht('Text'))
        ->setDescription(pht('The main body text of the paste.'))
        ->setTransactionType(PhabricatorPasteTransaction::TYPE_CONTENT)
        ->setMonospaced(true)
        ->setHeight(AphrontFormTextAreaControl::HEIGHT_VERY_TALL)
        ->setValue($object->getRawContent()),
      id(new PhabricatorSelectEditField())
        ->setKey('status')
        ->setLabel(pht('Status'))
        ->setDescription(pht('Active or archive the paste.'))
        ->setTransactionType(PhabricatorPasteTransaction::TYPE_STATUS)
        ->setIsConduitOnly(true)
        ->setValue($object->getStatus())
        ->setOptions(PhabricatorPaste::getStatusNameMap()),
    );
  }

}
