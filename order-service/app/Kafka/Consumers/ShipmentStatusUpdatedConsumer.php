<?php

namespace App\Kafka\Consumers;

use App\Actions\UpdateOrderStatus;
use Illuminate\Console\Command;
use Junges\Kafka\Contracts\MessageConsumer;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\ConsumedMessage;
use Throwable;

class ShipmentStatusUpdatedConsumer extends Command
{
    protected $signature = 'kafka:consume-shipment-status-updated';
    protected $description = 'Consume Kafka topic messages';

    public function __construct(private UpdateOrderStatus $action) 
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Initializing consumer of shipment status updated');

        Kafka::consumer()
            ->subscribe('shipment.updated')
            ->withManualCommit()
            ->withConsumerGroupId('order_service_consumer')
            ->withHandler(function (ConsumedMessage $message, MessageConsumer $consumer) {
                try {
                    $data = $message->getBody();

                    $this->action->handle($data);

                    $consumer->commit($message);

                    $this->info("Order #{$data['order_id']} status updated to: {$data['status']}");
                } catch (Throwable $e) {
                    $this->error("Failed to process shipment status update, sending to DLQ. Message: " . $e->getMessage());
                    throw $e;
                }
            })
            ->build()
            ->consume();

        return Command::SUCCESS;
    }
}