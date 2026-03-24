<?php

namespace App\Console\Commands\Consumers;

use App\Actions\CreateShipment;
use Illuminate\Console\Command;
use Junges\Kafka\Contracts\MessageConsumer;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\ConsumedMessage;
use Throwable;

class OrderPlacedConsumer extends Command
{
    protected $signature = 'kafka:consume-order-placed';
    protected $description = 'Consume Kafka topic messages';

    public function __construct(private CreateShipment $action) 
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Initializing consumer of template requested...');

        Kafka::consumer()
            ->subscribe('order.placed')
            ->withDlq('order.placed-dlq')
            ->withManualCommit()
            ->withConsumerGroupId('shipping_service_consumer')
            ->withHandler(function (ConsumedMessage $message, MessageConsumer $consumer) {
                try {
                    $data = $message->getBody();

                    $this->action->handle($data);

                    $consumer->commit($message);

                    $this->info("Order processed: " . $data['order_id']);
                } catch (Throwable $e) {
                    $this->error("Failed to process order, sending to DLQ. Message: " . $e->getMessage());
                    throw $e;
                }
            })
            ->build()
            ->consume();

        return Command::SUCCESS;
    }
}