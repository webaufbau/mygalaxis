<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Deploy extends BaseConfig
{
    public string $remoteRepository = 'git@github.com:user/repo.git';
    public string $branch = 'refs/heads/main';
    public string $dir = '/var/www/html/myrepo/';
    public string $logfile = WRITEPATH . 'logs/deploy.log';
    public string $gitPath = '/usr/bin/git';
    public string $token = '';  // z.B. aus .env
    public bool $runComposerUpdate = false;
    public int $maxExecutionTime = 180;
    public string $beforePull = '';
    public string $afterPull = '';
}
