<?php

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';

require_once $basePath . '/maintenance/Maintenance.php';

class BulkUnprotectMaintenance extends Maintenance {

    public function execute() {

        $this->output("Gathering protected pages..");
        $dbw = wfGetDB(DB_MASTER);
        $protectedPagesResult = $dbw->select(
            'page_restrictions',
            '*',
            array(
                'pr_type' => 'edit'
            )
        );

        if( !$protectedPagesResult || !$protectedPagesResult->numRows() ) {
            $this->output("\nNothing to do, exiting.");
            return;
        }

        while ($row = $protectedPagesResult->fetchRow()) {
            $page_id = $row['pr_page'];
            $page = Article::newFromID( $page_id );
            if( !$page || !$page->getTitle()->exists() ) {
                $this->output("\nSkipping page {$page_id}..");
                continue;
            }
            $pagetitle = $page->getTitle()->getBaseText();
            $this->output("\nWorking with '{$pagetitle}' ({$page_id})");
            $cascade = false;
            $page->doUpdateRestrictions(
                array(
                    'edit' => ''
                ),
                array(
                    'edit' => ''
                ),
                $cascade,
                'Unprotected by wiki-bot',
                User::newFromId(0)
            );
        }

        $this->output("\n");

    }

}

$maintClass = 'BulkUnprotectMaintenance';
require_once ( RUN_MAINTENANCE_IF_MAIN );