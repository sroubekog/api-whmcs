<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\WHMCS\Subsystems;

use JuniWalk\WHMCS\Enums\InvoiceStatus;
use JuniWalk\WHMCS\Enums\Sort;
use JuniWalk\WHMCS\Tools\ItemIterator;
use Nette\Schema\Expect;

trait BillingSubsystem
{
	/**
	 * @see https://developers.whmcs.com/api-reference/addtransaction/
	 */
	public function addTransaction(string $paymentMethod, iterable $params): bool
	{
		$params['paymentmethod'] = $paymentMethod;
		$params = $this->check($params, [
			'paymentmethod'			=> Expect::string()->required(),
			'userid'				=> Expect::int(),
			'invoiceid'				=> Expect::int(),
			'transid'				=> Expect::string(),
			'date'					=> Expect::string(),
			'currencyid'			=> Expect::int(),
			'description'			=> Expect::string(),
			'amountin'				=> Expect::float(),
			'fees'					=> Expect::float(),
			'amountout'				=> Expect::float(),
			'rate'					=> Expect::float(),
			'credit'				=> Expect::bool(),
			'allowduplicatetransid'	=> Expect::bool(),
		]);

		$response = $this->call('AddTransaction', $params);
		return $response['result'] === 'success';
	}


	/**
	 * @see https://developers.whmcs.com/api-reference/gettransactions/
	 */
	public function getTransactions(
		int $invoiceId = null,
		int $clientId = null,
		string $transactionId = null
	): ItemIterator {
		$data = $this->call('GetTransactions', [
			'invoiceid' => $invoiceId,
			'clientid' => $clientId,
			'transid' => $transactionId,
		]);

		$items = new ItemIterator($data['transactions']['transaction'] ?? []);
		$items->setTotalResults($data['totalresults']);
		$items->setOffset($data['startnumber']);
		$items->setLimit($data['numreturned']);
		return $items;
	}


	/**
	 * @see https://developers.whmcs.com/api-reference/getinvoice/
	 */
	public function getInvoice(int $invoiceId): iterable {
		return $this->call('GetInvoice', [
			'invoiceid' => $invoiceId,
		]);
	}


	/**
	 * @see https://developers.whmcs.com/api-reference/getinvoices/
	 */
	public function getInvoices(
		int $userId = null,
		InvoiceStatus $status = null,
		string $orderBy = null,
		Sort $sort = Sort::ASC,
		int $offset = 0,
		int $limit = 25
	): ItemIterator {
		$data = $this->call('GetInvoices', [
			'limitstart' => $offset,
			'limitnum' => $limit,
			'userid' => $userId,
			'status' => $status?->name,
			'orderby' => $orderBy,
			'order' => $sort->name,
		]);

		$items = new ItemIterator($data['invoices']['invoice'] ?? []);
		$items->setTotalResults($data['totalresults']);
		$items->setOffset($data['startnumber']);
		$items->setLimit($data['numreturned']);
		return $items;
	}


	/**
	 * @param  int $invoiceId
	 * @param  mixed[]  $params
	 * @return bool
	 * @see https://developers.whmcs.com/api-reference/updateinvoice/
	 */
	public function updateInvoice(int $invoiceId, iterable $params): bool
	{
		$params['invoiceid'] = $invoiceId;
		$params = $this->check($params, [
			'invoiceid'				=> Expect::int()->required(),
			'status'				=> Expect::type(InvoiceStatus::class),
			'paymentmethod'			=> Expect::string(),
			'taxrate'				=> Expect::float(),
			'taxrate2'				=> Expect::float(),
			'credit'				=> Expect::float(),
			'date'					=> Expect::string(),
			'duedate'				=> Expect::string(),
			'datepaid'				=> Expect::string(),
			'note'					=> Expect::string(),
			'itemdescription'		=> Expect::arrayOf('string'),
			'itemamount'			=> Expect::arrayOf('float'),
			'itemtaxed'				=> Expect::arrayOf('bool'),
			'newitemdescription'	=> Expect::arrayOf('string'),
			'newitemamount'			=> Expect::arrayOf('float'),
			'newitemtaxed'			=> Expect::arrayOf('bool'),
			'deletelineids'			=> Expect::arrayOf('int'),
			'publish'				=> Expect::bool(),
			'publishandsendemail'	=> Expect::bool(),
		]);

		$response = $this->call('UpdateInvoice', $params);
		return $response['result'] === 'success';
	}
}
