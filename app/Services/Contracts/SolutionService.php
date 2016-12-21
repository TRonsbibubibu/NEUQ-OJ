<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-21
 * Time: 下午3:33
 */

namespace NEUQOJ\Services\Contracts;


use NEUQOJ\Repository\Eloquent\CompileInfoRepository;
use NEUQOJ\Repository\Eloquent\RuntimeInfoRepository;
use NEUQOJ\Repository\Eloquent\SolutionRepository;
use NEUQOJ\Repository\Eloquent\SourceCodeRepository;

class SolutionService implements SolutionServiceInterface
{
    private $solutionRepo;
    private $compileInfoRepo;
    private $runtimeInfoRepo;
    private $sourceCodeRepo;

    public function __construct(
        SolutionRepository $solutionRepository,CompileInfoRepository $compileInfoRepository,
        RuntimeInfoRepository $runtimeInfoRepository,SourceCodeRepository $sourceCodeRepository
    )
    {
        $this->solutionRepo = $solutionRepository;
        $this->compileInfoRepo = $compileInfoRepository;
        $this->runtimeInfoRepo = $runtimeInfoRepository;
        $this->sourceCodeRepo = $sourceCodeRepository;
    }

    public function getAllSolutions(int $page, int $size)
    {
        return $this->solutionRepo->paginate($page,$size,[],[
            'id','problem_id','user_id','time','memory','result','language','code_length','created_time'
        ]);
    }

    public function getSolution(int $solutionId, array $columns = ['*'])
    {
        return $this->solutionRepo->get($solutionId,$columns)->first();
    }

    public function getSolutionBy(string $param, $value, array $columns = ['*'])
    {
        return $this->solutionRepo->getBy($param,$value,$columns);
    }

    public function getSolutionCount(): int
    {
       return $this->solutionRepo->getTotalCount();
    }

    public function getCompileInfo(int $solutionId)
    {
        return $this->compileInfoRepo->get($solutionId,['*'],'solution_id')->first();
    }

    public function getRuntimeInfo(int $solutionId)
    {
        return $this->runtimeInfoRepo->get($solutionId,['*'],'solution_id')->first();
    }

    public function getSourceCode(int $solutionId)
    {
        return $this->sourceCodeRepo->get($solutionId,['*'],'solution_id')->first();
    }

    public function isSolutionExist(int $solutionId):bool
    {
        $solution = $this->solutionRepo->get($solutionId,['id']);

        if($solution == null)
            return false;
        return true;
    }
}