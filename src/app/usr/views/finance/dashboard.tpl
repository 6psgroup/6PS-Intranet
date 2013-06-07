{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

{if $msg != ''}
	<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
{/if}

<div class="wrap">
	<h2>{$block_title}</h2>
	
	<h3>FOREX Markets</h3>
	<table cellpadding="0" cellspacing="0" border="0" width="95%" align="center">
		<tr>
			<td>
				<h4>USD-BRL (Brazil Reais)</h4>
				
				<table cellpadding="0" cellspacing="0" border="0" width="40%">
					<tr>
						<td><strong>Last Trade:</strong></td>
						<td><strong>{$BRLLastTrade}</strong></td>
					</tr>
					<tr>
						<td><font size="-1">Bid:</font></td>
						<td><font size="-1">{$BRLBid}</font></td>
					</tr>
					<tr>
						<td><font size="-1">Ask:</font></td>
						<td><font size="-1">{$BRLAsk}</font></td>
					</tr>
				</table>
				<a href="http://finance.yahoo.com/q?s=USDBRL=X" target="_blank">
					<img src="http://ichart.finance.yahoo.com/3m?usdbrl=x" border="0" width="410" height="230" />
				</a>
			</td>
			<td>
				<h4>USD-JPY (Japan Yen)</h4>
				
				<table cellpadding="0" cellspacing="0" border="0" width="40%">
					<tr>
						<td><strong>Last Trade:</strong></td>
						<td><strong>{$JPYLastTrade}</strong></td>
					</tr>
					<tr>
						<td><font size="-1">Bid:</font></td>
						<td><font size="-1">{$JPYBid}</font></td>
					</tr>
					<tr>
						<td><font size="-1">Ask:</font></td>
						<td><font size="-1">{$JPYAsk}</font></td>
					</tr>
				</table>
	
				<a href="http://finance.yahoo.com/q?s=USDJPY=X" target="_blank">
					<img src="http://ichart.finance.yahoo.com/3m?usdjpy=x" border="0" width="410" height="230" />
				</a>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<table cellpadding="0" cellspacing="0" border="0" width="50%">
					<tr>
						<td>
							<h4>EUR-USD (Euro)</h4>
							
							<table cellpadding="0" cellspacing="0" border="0" width="40%">
								<tr>
									<td><strong>Last Trade:</strong></td>
									<td><strong>{$EURLastTrade}</strong></td>
								</tr>
								<tr>
									<td><font size="-1">Bid:</font></td>
									<td><font size="-1">{$EURBid}</font></td>
								</tr>
								<tr>
									<td><font size="-1">Ask:</font></td>
									<td><font size="-1">{$EURAsk}</font></td>
								</tr>
							</table>
				
							<a href="http://finance.yahoo.com/q?s=EURUSD=X" target="_blank">
								<img src="http://ichart.finance.yahoo.com/3m?eurusd=x" border="0" width="410" height="230" />
							</a>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>

{include file="admin/adminFooter.tpl"}