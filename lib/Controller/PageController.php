<?php

declare(strict_types=1);

namespace OCA\CatGifs\Controller;


use Exception;
use OCP\App\IAppManager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\Constants;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IServerContainer;
use OCP\IURLGenerator;
use OCP\PreConditionNotMetException;
use Psr\Log\LoggerInterface;
use Throwable;

use OCA\CatGifs\AppInfo\Application;
use OCA\CatGifs\Service\ImageService;

class PageController extends Controller
{
	public const FIXED_GIF_SIZE_CONFIG_KEY = 'fixed_gif_size';

	public const CONFIG_KEYS = [
		self::FIXED_GIF_SIZE_CONFIG_KEY,
	];

	private $initialStateService;

	private $config;

	private $userID;

	public function  __construct(
		string $appName,
		IRequest $request,
		IInitialState $initialStateService,
		IConfig $config,
		?string $userId
	) {
		parent::__construct($appName, $request);
		$this->initialStateService = $initialStateService;
		$this->config = $config;
		$this->userID = $userId;
	}
	#[NoCSRFRequired]
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/')]
	public function mainPage(): TemplateResponse
	{
		$fileName = $this->getGifFileNameList();
		$fixedGifSize = $this->config->getUserValue($this->userID, Application::APP_ID, self::FIXED_GIF_SIZE_CONFIG_KEY);
		$myInitialState = [
			'file_name_list' => $fileName,
			self::FIXED_GIF_SIZE_CONFIG_KEY => $fixedGifSize,
		];
		$this->initialStateService->provideInitialState('tutorial_initial_state', $myInitialState);

		$appVersion = $this->config->getAppValue(Application::APP_ID, 'installed_version');
		return new TemplateResponse(
			Application::APP_ID,
			'index',
			[
				'app_version' => $appVersion,
			]
		);
	}
	private function getGifFilenameList(): array
	{
		$path = dirname(__DIR__, 2) . '/img/gifs';
		$names = array_filter(scandir($path), static function ($name) {
			return $name !== '.' && $name !== '..';
		});
		return array_values($names);
	}

	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'PUT', url: '/config')]

	public function saveConfig(string $key, string $value): DataResponse
	{
		if (in_array($key, self::CONFIG_KEYS, true)) {
			$this->config->setUserValue($this->userID, Application::APP_ID, $key, $value);
			return new DataResponse([
				'message' => 'Everything went fine'
			]);
		}
		return new DataResponse([
			'error_message' => 'Invalid config key',
		], Http::STATUS_FORBIDDEN);
	}
}
