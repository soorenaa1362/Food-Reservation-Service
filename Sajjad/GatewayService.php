<?php

namespace App\Services;

use App\Clients\Gateways\GatewaySaman;
use App\Exceptions\AccessDeniedException;
use App\Models\PaymentGateway;
use App\Repositories\GatewayRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ReserveRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GatewayService extends BaseService
{

    public function __construct(
        protected GatewaySaman      $gatewaySaman,
        protected ReserveRepository $reserveRepository,
        protected PaymentRepository $paymentRepository,
        protected GatewayRepository $gatewayRepository
    )
    {
    }

    // his  side ---------------------------------------------------------------------------------------------------
    public function create(array $data): PaymentGateway
    {
        return $this->gatewayRepository->createGateway($data);
    }

    public function getAllByCenter(int $centerId): Collection
    {
        return $this->gatewayRepository->getAllByCenter($centerId);
    }

    public function update(array $data): ?PaymentGateway
    {
        $gateway = $this->gatewayRepository->findGateway($data['id']);
        if (!$gateway) return null;
        $user = Auth::guard('api')->user();
        if(!($user->hasRoleInCenter('admin', $gateway->center_id))) throw new AccessDeniedException();
        return $this->gatewayRepository->updateGateway($gateway, $data);
    }

    public function delete(array $data): bool
    {
        $gateway = $this->gatewayRepository->findGateway($data['id']);
        if (!$gateway) return false;
        $user = Auth::guard('api')->user();
        if(!($user->hasRoleInCenter('admin', $gateway->center_id))) throw new AccessDeniedException();
        return $this->gatewayRepository->safeDelete($gateway);
    }


    // user side ---------------------------------------------------------------------------------------------------
    public function getActiveByCenter(int $centerId): Collection
    {
        return $this->gatewayRepository->getActiveByCenter($centerId);
    }

    public function getTokenFromBank(int $reserveId, int $amount, $gateway, ?string $phone = null)
    {
        return $this->gatewaySaman->getToken($reserveId, $amount, $gateway, $phone)['token'];
    }

    public function verifyTransaction(string $RefNum, int $terminalId)
    {
        $realResultFromBank = (array) $this->gatewaySaman->verifyTransaction(
            $RefNum,
            $terminalId
        );

        return [
            'Amount' => $realResultFromBank['TransactionDetail']['AffectiveAmount'],
            'StraceDate' => $realResultFromBank['TransactionDetail']['StraceDate'],
            'StraceNo' => $realResultFromBank['TransactionDetail']['StraceNo'],
        ];
    }

    public function reverseTransaction(string $RefNum, int $terminalId)
    {
        return $this->gatewaySaman->reverseTransaction(
            $RefNum,
            $terminalId
        );
    }
}
