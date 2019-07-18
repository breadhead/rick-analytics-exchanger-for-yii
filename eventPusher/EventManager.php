<?php
namespace breadhead\rickAnalytics\eventPusher;

use breadhead\rickAnalytics\api\entities\Deal;
use breadhead\rickAnalytics\api\entities\Item;
use breadhead\rickAnalytics\api\entities\User;
use breadhead\rickAnalytics\api\RickDataExchanger;
use breadhead\rickAnalytics\eventLogger\Event;
use breadhead\rickAnalytics\eventLogger\EventRepository;
use breadhead\rickAnalytics\eventLogger\EventRepositoryException;

class EventManager
{
    private $rickDataExchanger;
    private $eventRepository;
    private $jobs = [];
    private $jobsLimit = 100;

    public function __construct(RickDataExchanger $rickDataExchanger, EventRepository $eventRepository, ?int $jobsLimit = 100)
    {
        $this->rickDataExchanger = $rickDataExchanger;
        $this->eventRepository = $eventRepository;
        if ($jobsLimit) {
            $this->jobsLimit = $jobsLimit;
        }
    }

    public function executeEvents()
    {
        try {
            $this->defineJobs();

            array_map(
                function ($event) {
                    $this->doJob($event);
                },
                $this->jobs
            );
        } catch (EventRepositoryException $e) {
            throw new EventManagerException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function defineJobs(): void
    {
        $inProgress = $this->eventRepository->find(['status' => Event::STATUS_IN_PROGRESS], 1);

        if (!empty($inProgress)) {
            exit;
        }

        $jobs = $this->eventRepository->find(['status' => Event::STATUS_NEW], $this->jobsLimit);

        $this->jobs = $jobs == null ? [] : $jobs;
    }

    private function doJob(Event $event): bool
    {
        try {
            $event->changeStatus(Event::STATUS_IN_PROGRESS);
            $this->eventRepository->update($event);

            $data = $event->getData();

            switch ($event->getEventType()) {
                case Event::TYPE_REGISTER:
                    /** @var RickDataExchanger $r*/
                    $rickUser = new User($data['user_id'], $data['client_id'], $data['timestamp']);

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
                        $data
                    );

                    $this->rickDataExchanger->checkDeals($deals);

                    break;

                default:
                    throw new EventManagerException(sprintf('Unknown event type %s was given', $event->getEventType()));
            }

            $event->changeStatus(Event::STATUS_DONE);
            $this->eventRepository->update($event);

        } catch (\Exception $e) {
            $event->changeStatus(Event::STATUS_FAIL);
            $event->changeError($e->getMessage());

            $this->eventRepository->update($event);

            throw new EventManagerException($e->getMessage(), $e->getCode(), $e);
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
        $items = [];
        if (isset($data['items']) && !empty($data['items'])) {
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
        try {
            $event = new Event(
                null,
                Event::TYPE_CREATE,
                $deal->getAsArray(),
                $deal->clientId,
                Event::STATUS_NEW,
                $deal->orderId,
                null,
                null,
                null
            );

            $this->eventRepository->insert($event);
        } catch (EventRepositoryException $e) {
            throw new EventManagerException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function addDealUpdateEvent(Deal $deal): void
    {
        try {
            $event = new Event(
                null,
                Event::TYPE_UPDATE,
                $deal->getAsArray(),
                $deal->clientId,
                Event::STATUS_NEW,
                $deal->orderId,
                null,
                null,
                null
            );

            $this->eventRepository->insert($event);
        } catch (EventRepositoryException $e) {
            throw new EventManagerException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function addDealCreteOrUpdateEvent(Deal $deal): void
    {
        try {
            $event = $this->eventRepository->findOne(['deal_id' => $deal->orderId, 'event_type' => Event::TYPE_CREATE]);

            if ($event) {
                $this->addDealUpdateEvent($deal);
            } else {
                $this->addDealCreateEvent($deal);
            }
        } catch (EventRepositoryException $e) {
            throw new EventManagerException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function addUserRegisterEvent(User $user): void
    {
        try {
            $event = new Event(
                null,
                Event::TYPE_REGISTER,
                $user->getAsArray(),
                $user->clientId,
                Event::STATUS_NEW,
                null,
                null,
                null,
                null
            );

            $this->eventRepository->insert($event);
        } catch (EventRepositoryException $e) {
            throw new EventManagerException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function addDealCheckEvent(iterable $deals)
    {
        try {
            $deals = (function (Deal ...$deals) {
                return $deals;
            }) (...$deals);

            $data = [];
            array_map(
                function($deal) use(&$data) {
                    /** @var Deal $deal */
                    $data[] = $deal->getAsArray();
                },
                $deals
            );

            $event = new Event(
                null,
                Event::TYPE_CHECK,
                $data,
                'check_event',
                Event::STATUS_NEW,
                null,
                null,
                null,
                null
            );

            $this->eventRepository->insert($event);
        } catch (EventRepositoryException $e) {
            throw new EventManagerException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
