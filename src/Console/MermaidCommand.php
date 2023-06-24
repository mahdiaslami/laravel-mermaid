<?php

namespace MahdiAslami\Database\Console;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'mermaid')]
class MermaidCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mermaid {--database= : The database connection}
                    {--link : Show link to mermaid editor}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display mermaid diagram about the given database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ConnectionResolverInterface $connections)
    {
        $connection = $connections->connection($this->input->getOption('database'));
        $schema = $connection->getDoctrineSchemaManager();

        $tables = $this->tables($schema);

        $diagramCode = $this->diagramCode($tables);

        if ($this->option('link')) {
            $this->info($this->mermaidEditorUrl($diagramCode));
        } else  {
            $this->info($diagramCode);
        }

        return 0;
    }

    private function mermaidEditorUrl(string $code): string
    {
        $config = json_encode([
            'code' => $code,
            'mermaid' => json_encode([
                'theme' => 'dark',
            ]),
            'autoSync' => true,
            'updateDiagram' => true,
            'panZoom' => true,
            'pan' => [
                'x' => 0,
                'y' => 0,
            ],
            'zoom' => 1.0,
            'editorMode' => 'code',
        ]);

        $compressedData = gzcompress($config);
        $encodedData = base64_encode($compressedData);
        $url = 'https://mermaid.live/edit#pako:'.$encodedData;

        return $url;
    }

    private function diagramCode(Collection $tables): string
    {
        $data = $this->prepareData($tables);

        return $this->prepareCode($data);
    }

    private function prepareData(Collection $tables)
    {
        $tables = $tables->sortBy([
            ['relatedsCount', 'desc'],
            ['foreignsCount', 'desc'],
            ['name', 'asc'],
        ]);
        $hashMap = $tables->keyBy('name');

        $relations = collect([]);
        $queue = collect([$tables->first()->name]);
        $shifted = [];

        while ($queue->count() > 0) {
            $current = $queue->shift();

            while (! is_null($current)) {
                $relateds = $hashMap->get($current)
                    ->relatedTables
                    ->map(fn ($name) => $hashMap->get($name))
                    ->sortBy(['relatedsCount', 'foreignsCount', 'name'])
                    ->map(fn ($table) => $table->name);

                $relations = $relations->concat(
                    $relateds->map(fn ($related) => $this->oneToMany($current, $related))
                );

                $shifted[] = $current;

                $queue = $queue->concat(
                    $relateds->filter(fn ($related) => ! in_array($related, $shifted))
                );

                $current = $queue->shift();
            }

            $table = $tables->filter(fn ($table) => ! in_array($table->name, $shifted))
                ->filter(fn ($table) => $table->relatedsCount > 0)
                ->first();

            if ($table) {
                $queue->push($table->name);
            }
        }

        return (object) [
            'relations' => $relations,
            'singleTables' => $tables->filter(fn ($table) => ! in_array($table->name, $shifted))
                ->map(fn ($table) => $table->name),
        ];
    }

    private function oneToMany($start, $end)
    {
        return "{$start} ||--|{ {$end} : OneToMany";
    }

    private function prepareCode($data)
    {
        return "erDiagram\n    "
            .$data->relations->implode("\n    ")
            ."\n    "
            .$data->singleTables->implode("\n    ");
    }

    protected function tables(AbstractSchemaManager $schema): Collection
    {
        $tables = collect($schema->listTables())
            ->map(fn ($table) => (object) [
                'name' => $table->getName(),
                'foreignTables' => collect($table->getForeignKeys())
                    ->values()
                    ->map(fn ($foreignKey) => $foreignKey->getForeignTableName()),
            ]);

        $tables = $tables->map(function ($table) use ($tables) {
            $table->relatedTables = $tables->filter(
                fn ($related) => $related->foreignTables->contains($table->name)
            )->pluck('name');

            return $table;
        });

        return $tables->map(function ($table) {
            $table->foreignsCount = $table->foreignTables->count();
            $table->relatedsCount = $table->relatedTables->count();

            return $table;
        });
    }
}
