<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Jobs\ImportDiscoveredJobs as Importer;
use Illuminate\Console\Command;

final class ImportDiscoveredJobs extends Command
{
    protected $signature = 'jobs:import-discovered {json-file} {--user= : Existing workspace user email or ID} {--dry-run}';

    protected $description = 'Validate and import discovered jobs into the private workspace';

    public function handle(Importer $importer): int
    {
        if (! $this->option('user')) {
            $this->error('The --user option is required. No user is created automatically.');

            return self::INVALID;
        }
        $user = User::query()->where('email', $this->option('user'))->orWhere('id', $this->option('user'))->first();
        if (! $user) {
            $this->error('Workspace user not found.');

            return self::FAILURE;
        }
        $path = realpath($this->argument('json-file'));
        if (! $path || ! is_readable($path)) {
            $this->error('JSON file is not readable.');

            return self::INVALID;
        }
        try {
            $decoded = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $this->error('Malformed JSON.');

            return self::INVALID;
        }
        $records = array_is_list($decoded) ? $decoded : ($decoded['jobs'] ?? null);
        if (! is_array($records)) {
            $this->error('Expected a JSON array or a jobs array.');

            return self::INVALID;
        }
        $report = $importer->import($user, $records, (bool) $this->option('dry-run'));
        $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $report['invalid'] ? self::FAILURE : self::SUCCESS;
    }
}
