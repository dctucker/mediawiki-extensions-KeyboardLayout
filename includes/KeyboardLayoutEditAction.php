<?php

class KeyboardLayoutEditAction extends \EditAction {
	public function show() {
		$this->useTransactionalTimeLimit();
		$editPage = new KeyboardLayoutEditPage( $this->getArticle() );
		$editPage->setContextTitle( $this->getTitle() );
		$editPage->edit();
	}
}
