<?php
/**
 * Created by PhpStorm.
 * User: Azhe
 * Date: 22/09/2018
 * Time: 15.48
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use WinTenDev\Handlers\ChatHandler;
use WinTenDev\Model\Group;
use WinTenDev\Model\Tags;
use WinTenDev\Utils\Entities;

class TagCommand extends UserCommand
{
	protected $name = 'tag';
	protected $description = 'Save tag into cloud';
	protected $usage = '/tag <tagnya>';
	protected $version = '1.0.0';
	
	/**
	 * Execute command
	 *
	 * @return ServerResponse
	 * @throws TelegramException
	 */
	public function execute()
	{
		$message = $this->getMessage();
		$chatHandler = new ChatHandler($message);
		$chatid = $message->getChat()->getId();
		$fromid = $message->getFrom()->getId();
		$pecah = explode(' ', $message->getText(true));
		$repMssg = $message->getReplyToMessage();
		
		$isAdmin = Group::isAdmin($fromid, $chatid);
		$isSudoer = Group::isSudoer($fromid);
		if ($isAdmin || $isSudoer) {
			$chatHandler->sendText('Mempersiapkan..');
			if (strlen($pecah[0]) >= 3) {
				$tag = Tags::clearTag($pecah[0]);
				$datas = [
					'tag'     => $tag,
					'id_user' => $fromid,
					'id_chat' => $chatid,
				];
				
				$tipe_data = 'text';
				if ($repMssg !== null) {
//                    $konten = $repMssg->getText() ?? $repMssg->getCaption();
					$konten = Entities::getHtmlFormatting($message);
					
					if ($repMssg->getSticker()) {
						$tipe_data = 'sticker';
						$id_data = $repMssg->getSticker()->getFileId();
					} elseif ($repMssg->getDocument()) {
						$tipe_data = 'document';
						$id_data = $repMssg->getDocument()->getFileId();
					} elseif ($repMssg->getVideo()) {
						$tipe_data = 'video';
						$id_data = $repMssg->getVideo()->getFileId();
					} elseif ($repMssg->getVideoNote()) {
						$tipe_data = 'videonote';
						$id_data = $repMssg->getVideoNote()->getFileId();
					} elseif ($repMssg->getVoice()) {
						$tipe_data = 'voice';
						$id_data = $repMssg->getVoice()->getFileId();
					} elseif ($repMssg->getPhoto()) {
						$tipe_data = 'photo';
						$id_data = $repMssg->getPhoto()[0]->getFileId();
					}
					
					$btn_data = ltrim($message->getText(true), $pecah[0]);
				} else {
					//$konten = ltrim($message->getText(true), $pecah[0]);
					$konten = Entities::getHtmlFormatting($message);
				}
				
				$datas += [
					'content'   => $konten ?? '',
					'type_data' => $tipe_data,
					'id_data'   => $id_data ?? '',
					'btn_data'  => $btn_data ?? '',
				];
				
				$chatHandler->editText("Menyimpan {$tag}..");
				$add = Tags::saveTag($datas);
				if ($add->rowCount() > 0) {
					$text = "Menyimpan tag #{$tag} berhasil";
				} else {
					$text = 'Tag tidak di perbarui, kemungkinan data tag sama.';
				}
				$chatHandler->editText($text);
			} else {
				$text = 'ℹ  Simpan tag ke Cloud Tags' .
					"\n<b>Contoh:\n</b><code>/tag tagnya [button|link.button]</code> - Reply pesan" .
//                    "\n<code>/tag tag content</code> - InMessage" .
					"\nPanjang <code>tag</code> minimal 3 karakter.\nTanda [ ] artinya tidak harus";
			}
			
			$r = $chatHandler->editText($text);
		}
		return $r;
	}
}
