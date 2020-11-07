<?php

namespace SV\NoticeTimeReplacable;

use SV\StandardLib\InstallerHelper;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;

class Setup extends AbstractSetup
{
    use InstallerHelper;
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function upgrade2020000Step1()
    {
        $this->renamePhrases([
            'sv_notice_time_replacable_tags' => 'svNoticeTime_replacable_tags',
        ]);
    }
}
