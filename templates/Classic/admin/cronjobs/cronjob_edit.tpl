$header
	<form method="post" action="{$linker->getLink(array('section' => 'cronjobs'))}">
		<input type="hidden" name="s" value="$s" />
		<input type="hidden" name="page" value="$page" />
		<input type="hidden" name="action" value="$action" />
		<input type="hidden" name="id" value="$id" />
		<input type="hidden" name="send" value="send" />
		<table cellpadding="5" cellspacing="4" border="0" align="center" class="maintable">
			<tr>
				<td class="maintitle" colspan="2"><b><img src="templates/{$theme}/assets/img/title.gif" alt="{$title}" />&nbsp;{$title}</b></td>
			</tr>
			{$cronjobs_edit_form}
		</table>
	</form>
	<br />
	<br />
$footer
