# BrauneDigitalQueryFilterBundle
This Symfony-Bundle can be used to use the speed of sql queries in your frontend or other parts of your application

## Branches
- Symfony 2/3 use Branch 1.0.x
- Symfony 4|5 use Branch 1.4.x

## Usage

```
<?php

class EventListController extends AbstractController
{
    
    /** @var EventRepository */
    protected $eventRepository;

    /**
     * @param EventRepository $eventRepository
     */
    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @Route(
     *     "/events"
     * )
     * @param Request $request
     * @return Response
     */
    public function __invoke(Request $request): Response
    {

        $qb = $this->eventRepository->createQueryBuilder('event');
        $qm = $this->get('bd_query_filter.query_manager');
        
        $filterConfiguration = json_decode($request->query->get('filter'), true);
        $orderConfiguration = json_decode($request->query->get('order'), true);
        
        // filter and order the query
        if ($filterConfiguration) {
            $qm->filter($qb, $filterConfiguration, $request->getLocale());
        }
        if ($orderConfiguration) {
            $qm->order($qb, $orderConfiguration, $request->getLocale());
        }
        
        return $this->render(
            'app/events.html.twig',
            [
                'events' => $qb->getQuery()->getResult()
            ]
        );

```

## TODO
* Create basic ReadMe

