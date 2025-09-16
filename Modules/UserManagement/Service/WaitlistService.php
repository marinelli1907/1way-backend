<?php

namespace Modules\UserManagement\Service;

use App\Service\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\UserManagement\Service\Interface\WaitlistServiceInterface;
use Modules\UserManagement\Repository\WaitlistRepositoryInterface;

class WaitlistService extends BaseService implements WaitlistServiceInterface
{
    protected $waitlistRepository;

    public function __construct(WaitlistRepositoryInterface $waitlistRepository)
    {
        parent::__construct($waitlistRepository);
        $this->waitlistRepository = $waitlistRepository;
    }

    public function getAll(
        array $relations = [],
        array $orderBy = ['createdAt' => 'desc'],
        int $limit = null,
        int $offset = null,
        bool $onlyTrashed = false,
        bool $withTrashed = false,
        array $withCountQuery = [],
        array $groupBy = []
    ): Collection|LengthAwarePaginator {
        $criteria = [];
        $searchData = [];

        // Apply search
        if (request()->has('search') && request('search') !== '') {
            $searchData = [
                'fields' => ['fullName', 'email', 'phone', 'role'],
                'value'  => request('search'),
            ];
        }

        return $this->waitlistRepository->getBy(
            criteria: $criteria,
            searchCriteria: $searchData,
            relations: $relations,
            orderBy: $orderBy,
            limit: $limit,
            offset: $offset,
            onlyTrashed: $onlyTrashed,
            withTrashed: $withTrashed,
            withCountQuery: $withCountQuery,
            groupBy: $groupBy
        );
    }
}
