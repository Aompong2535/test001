<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';
require_once 'Service/amb.php';
$api = new AMBAPI();

function dd_return($status, $message)
{
	$json = ['message' => $message];
    if($status)
    {
        http_response_code(200);
        die(json_encode($json));
    }
    else
    {
        http_response_code(400);
        die(json_encode($json));
    }
}

//////////////////////////////////////////////////////////////////////////

header('Content-Type: application/json; charset=utf-8;');

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if(empty(get_session()))
	{
		dd_return(false, "กรุณาเข้าสู่ระบบก่อนทำรายการ");
	}
	else
	{
		$provider = trim($_POST['provider']);
		$gamemode = trim($_POST['gamemode']);
		$isMobile = trim($_POST['isMobile']);
		$osMobile = trim($_POST['osMobile']);
		$gameId = trim($_POST['gameId']);
		$username = get_session();
		if($provider != "" && $username != "")
		{
			$q_1 = dd_q('SELECT * FROM user_tb WHERE (u_user = ? AND u_block_agent = 0)', [$username]);
			if ($q_1->rowCount() >= 1)
			{
				$row = $q_1->fetch(PDO::FETCH_ASSOC);
				if(!empty($row['u_agent_id']))
				{
					$data = $api->AMBGameLogin($row['u_agent_id'], $row['u_agent_pass'], $provider, $gamemode, $isMobile, base_url(), $osMobile, $gameId);
					if ($data->success == true)
					{
						$data = json_decode(json_encode($data->data), true);
						dd_return(true, $data['url']);
					}
					else
					{
						if (strpos($data->message, 'Balance more than zero') !== false)
						{
							dd_return(false, "ยอดคงเหลือเหลือ 0 บาท กรุณาเติมเงินก่อนเข้าเล่น");
						}
						else
						{
							dd_return(false, $data->message);
						}
					}
				}
				else
				{
					dd_return(false, "ไม่พบข้อมูล User AMB");
				}
			}
			else
			{
				dd_return(false, "ไม่พบข้อมูลผู้ใช้งานในระบบ");
			}
		}
		else
		{
			dd_return(false, "กรุณากรอกข้อมูลให้ครบ");
		}
	}
}
else
{
	dd_return(false, "Method '{$_SERVER['REQUEST_METHOD']}' not allowed!");
}
?>
