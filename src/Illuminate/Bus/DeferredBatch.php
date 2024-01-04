<?php

namespace Illuminate\Bus;

use Illuminate\Bus\ChainedBatch;
use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\SerializableClosure\SerializableClosure;

class DeferredBatch implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The closure to resolve the batch contents.
     *
     * @var SerializableClosure
     */
    public SerializableClosure $closure;

    /**
     * The name of the batch.
     *
     * @var string
     */
    public string $name;

    /**
     * The batch options.
     *
     * @var array
     */
    public array $options;

    /**
     * Create a new deferred batch instance.
     *
     * @param  \Laravel\SerializableClosure\SerializableClosure  $closure
     * @param  \Illuminate\Bus\PendingBatch  $batch
     * @return void
     */
    public function __construct(SerializableClosure $closure, PendingBatch $batch)
    {
        $this->closure = $closure;
        $this->name = $batch->name;
        $this->options = $batch->options;
    }

    public function handle()
    {
        $pendingBatch = app(Dispatcher::class)->batch(($this->closure)());

        $pendingBatch->name($this->name);

        $pendingBatch->options = $this->options;

        return (new ChainedBatch($pendingBatch))->handle();
    }
}
