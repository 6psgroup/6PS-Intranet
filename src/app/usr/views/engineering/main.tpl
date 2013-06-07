{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">
	<h2>{$block_title}</h2>
    
    <h3>GNAX Gold</h3>
    
    <div>
    	<A HREF="https://www.6ps.com/rtg/view.php?rid=5&iid=104" target="_blank">
        	<IMG
            	SRC="https://www.6ps.com/rtg/rtgplot.cgi?t1=ifInOctets_5&t2=ifOutOctets_5&iid=104&begin={$rtgStart}&end={$rtgEnd}&units=bits/s&factor=8&filled=yes"
                BORDER="0">
        </A>
    </div>
    
    <h3>GNAX Silver</h3>
    
    <div>
    	<A HREF="https://www.6ps.com/rtg/view.php?rid=4&iid=100" target="_blank">
        	<IMG
            	SRC="https://www.6ps.com/rtg/rtgplot.cgi?t1=ifInOctets_4&t2=ifOutOctets_4&iid=100&begin={$rtgStart}&end={$rtgEnd}&units=bits/s&factor=8&filled=yes"
                BORDER="0">
        </A>
    </div>
    
    
</div>

{include file="admin/adminFooter.tpl"}