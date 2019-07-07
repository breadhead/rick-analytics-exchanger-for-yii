<?php
namespace breadhead\rickAnalytics\eventPusher;

use breadhead\rickAnalytics\api\entities\Deal;
use breadhead\rickAnalytics\api\entities\Item;
use breadhead\rickAnalytics\api\entities\User;
use breadhead\rickAnalytics\api\RickDataExchanger;
use breadhead\rickAnalytics\eventLogger\Event;
use breadhead\rickAnalytics\eventLogger\EventRepository;

class eventManager
{
    private $rickDataExchanger;
    private $eventRepository;
    private $jobs = [];

    public function __construct(RickDataExchanger $rickDataExchanger, EventRepository $eventRepository)
    {
        $this->rickDataExchanger = $rickDataExchanger;
        $this->eventRepository = $eventRepository;
    }

    public function executeEvents()
    {
        $this->defineJobs();

        array_map(
            function($event) {
                $this->doJob($event);
            },
            $this->jobs
        );
    }

    private function defineJobs(): array
    {
        $inProgress = $this->eventRepository->find(['status' => Event::STATUS_IN_PROGRESS], 1);

        if (!empty($inProgress)) {
            return false;
        }

        $jobs = $this->eventRepository->find(['status' => Event::STATUS_NEW], 10);

        $this->jobs = $jobs;
    }

    private function doJob(Event $event): bool
    {
        try {
            $event->changeStatus(Event::STATUS_IN_PROGRESS);

            $data = $event->getData();

            switch ($event->getEventType()) {
                case Event::TYPE_REGISTER:
                    /** @var RickDataExchanger $r*/
                    $rickUser = new User($data['user_id'], $data['client_id'], $data['created_at']);

                    $this->rickDataExchanger->createUser($rickUser);

                    break;
                case Event::TYPE_CREATE:
                    $rickDeal = $this->compileDeal($data);

                    $this->rickDataExchanger->createDeal($rickDeal);

                    break;

                case Event::TYPE_UPDATE:
                    $rickDeal = $this->compileDeal($data);

                    $this->rickDataExchanger->updateDeal($rickDeal);

                    break;

                case Event::TYPE_CHECK:
                    $deals = [];
                    array_map(
                        function($deal) use(&$deals) {
                            $deals[] = $this->compileDeal($deal);
                        },
                        $data['deals']
                    );

                    $this->rickDataExchanger->checkDeals($deals);

                    break;

                default:
                    throw new EventManagerException(sprintf('Unknown event type %s was given', $event->getEventType()));
            }

            $event->changeStatus(Event::STATUS_DONE);

        } catch (\Exception $e) {
            throw $e;
        }

        return true;
    }

    private function compileItem(array $fields): Item
    {
        return new Item(
            $fields['name'],
            $fields['sku'],
            $fields['price'],
            $fields['quantity'],
            $fields['category']
        );
    }

    private function compileDeal(array $data): Deal
    {
        if (isset($data['items']) && !empty($data['items'])) {
            $items = [];
            array_map(
                function($item) use (&$items) {
                    $items[] = $this->compileItem($item);
                },
                $data['items']
            );
        }

        return new Deal(
            $data['order_id'],
            $data['user_id'],
            $data['client_id'],
            $data['created_at'],
            $data['updated_at'],
            $data['status'],
            $data['revenue'],
            $items
        );
    }

    public function addDealCreateEvent(Deal $deal): void
    {
        $event = new Event(
            null,
            Event::TYPE_CREATE,
            $deal->getAsArray(),
            $deal->clientId,
            $deal->orderId,
            Event::STATUS_NEW
        );

        $this->eventRepository->insert($event);
    }

    public function addDealUpdateEvent(Deal $deal): void
    {
        $event = new Event(
            null,
            Event::TYPE_UPDATE,
            $deal->getAsArray(),
            $deal->clientId,
            $deal->orderId,
            Event::STATUS_NEW
        );

        $this->eventRepository->insert($event);
    }

    public function addDealCreteOrUpdateEvent(Deal $deal): void
    {
        $event = $this->eventRepository->findOne(['deal_id' => $deal->orderId, 'event_type' => Event::TYPE_CREATE]);

        if ($event) {
            $this->addDealUpdateEvent($deal);
        } else {
            $this->addDealCreateEvent($deal);
        }
    }

    public function addUserRegisterEvent(User $user): void
    {
        $event = new Event(
            null,
            Event::TYPE_REGISTER,
            $user->getAsArray(),
            $user->clientId,
            null,
            Event::STATUS_NEW
        );

        $this->eventRepository->insert($event);
    }
}
