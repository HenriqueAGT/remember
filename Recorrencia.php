<?php 

//conecta banco
$mysqli = mysqli_connect('IP', 'banco', 'Senha', 'data');

$ano = date ('Y');
$mes = date ('m');

$sql = 'delete from recorrencia
where mes = month(CURDATE())';

mysqli_query($mysqli, $sql);

echo 'Mes Apagado'."\n";


$sql_l = 'SELECT 
cliente_mes.Site as site,
Count(DISTINCT(cliente_mes.CPF)) as clientes_unicos,
count(clientes.CPF) as clientes_recompra
from(select
        pedidos_parceiroCanal as Site,
        pedidos_cliente_cpfCnpj  as CPF,
        year(pedidos_dataEmissao) as Ano,
        month(pedidos_dataEmissao) as Mes,
        count(distinct(pedidos_cliente_cpfCnpj)) as Clientes_Unicos,
        count(*) as Pedidos
    from pedidos
    where pedidos_status_descricao in ("Pedido Enviado","Entrega Realizada","Pago","Pedido Faturado")
    and year(pedidos_dataEmissao) = "' . $ano .'"
    and month(pedidos_dataEmissao) =  "' . $mes . '"
    group by Site,CPF,Ano,Mes)cliente_mes
LEFT JOIN (select
                pedidos_parceiroCanal as Site,
                pedidos_cliente_cpfCnpj  as CPF
                from pedidos
                where pedidos_status_descricao in ("Pedido Enviado","Entrega Realizada","Pago","Pedido Faturado")
                and pedidos_dataEmissao >= (CURDATE()  - INTERVAL 180 day)
                and (month(pedidos_dataEmissao) < "' . $mes . '" and YEAR(pedidos_dataEmissao) = "' . $ano .'")
                group by Site,CPF) clientes
on cliente_mes.Site = clientes.Site and cliente_mes.CPF = clientes.CPF
group by cliente_mes.Site';

$result = mysqli_query($mysqli, $sql_l);

while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

    echo 'site:'. $row['site'] . ' Total:' . $row['clientes_unicos'] . 'clientes_recompra: ' . $row['clientes_recompra'] ."\n";


    $sql_e = 'INSERT INTO recorrencia (
        site,
        clientes_mes,
        clientes_recompra,
        recompra,
        mes,
        date_update
    )VALUES';
    
    $sql_e .= '("' .$row['site']."_6M".'",            
    "'.$row['clientes_unicos'].'",
    "'.$row['clientes_recompra'].'",
    "'.$row['clientes_recompra'] / $row['clientes_unicos'].'",
    "'.$mes.'",
    CURDATE())';
    

    mysqli_query($mysqli, $sql_e);

}

##################### recorrencia ultimos 12 meses 

$sql_l = 'SELECT 
cliente_mes.Site as site,
Count(DISTINCT(cliente_mes.CPF)) as clientes_unicos,
count(clientes.CPF) as clientes_recompra
from(select
        pedidos_parceiroCanal as Site,
        pedidos_cliente_cpfCnpj  as CPF,
        year(pedidos_dataEmissao) as Ano,
        month(pedidos_dataEmissao) as Mes,
        count(distinct(pedidos_cliente_cpfCnpj)) as Clientes_Unicos,
        count(*) as Pedidos
    from pedidos
    where pedidos_status_descricao in ("Pedido Enviado","Entrega Realizada","Pago","Pedido Faturado")
    and year(pedidos_dataEmissao) = "' . $ano .'"
    and month(pedidos_dataEmissao) =  "' . $mes . '"
    group by Site,CPF,Ano,Mes)cliente_mes
LEFT JOIN (select
                pedidos_parceiroCanal as Site,
                pedidos_cliente_cpfCnpj  as CPF
                from pedidos
                where pedidos_status_descricao in ("Pedido Enviado","Entrega Realizada","Pago","Pedido Faturado")
                and pedidos_dataEmissao >= (CURDATE()  - INTERVAL 180 day)
                and (month(pedidos_dataEmissao) < "' . $mes . '" and YEAR(pedidos_dataEmissao) = "' . $ano .'")
                group by Site,CPF
                union
                    SELECT 
                        Site,
                        CPF
                    from notas2021
                    where month(data_nota) >= "' . $mes . '"
                    group by Site,CPF
                    HAVING Site is not null
                ) clientes
on cliente_mes.Site = clientes.Site and cliente_mes.CPF = clientes.CPF
group by cliente_mes.Site';

$result = mysqli_query($mysqli, $sql_l);

while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

    echo 'site:'. $row['site'] . ' Total:' . $row['clientes_unicos'] . 'clientes_recompra: ' . $row['clientes_recompra'] ."\n";


    $sql_e = 'INSERT INTO recorrencia (
        site,
        clientes_mes,
        clientes_recompra,
        recompra,
        mes,
        date_update
    )VALUES';
    
    $sql_e .= '("' .$row['site']."_12M".'",            
    "'.$row['clientes_unicos'].'",
    "'.$row['clientes_recompra'].'",
    "'.$row['clientes_recompra'] / $row['clientes_unicos'].'",
    "'.$mes.'",
    CURDATE())';
    

    mysqli_query($mysqli, $sql_e);

}


##################### recorrencia por itens 

//conecta banco

$sql = 'delete from recorrencia_itens
where mes = month(CURDATE())';

mysqli_query($mysqli, $sql);

$sql = 'SELECT 
itens_mes.Site,
itens_mes.produto as Produto,
count(itens_mes.CPF) as Dentro_do_mes,
count(itens_antes.CPF) as Recorenci
FROM (SELECT 
pedidos_parceiroCanal as Site,
pedidos_cliente_cpfCnpj as CPF,
itens.produto,
count(DISTINCT(pedidos_cliente_cpfCnpj)) as total
from pedidos
inner join ( SELECT 
			pedido_site as Site,
			pedido_numeroPedido,
			complemento.produto as produto
			from pedidos_itens itens
			inner join pedidos_itens_complemento complemento
							on itens.pedido_sku = complemento.sku 
			where year(pedido_dataEmissao) = "'.$ano.'"
			and month(pedido_dataEmissao) = "'.$mes.'") itens
	on pedidos.pedidos_numeroPedido = itens.pedido_numeroPedido
where pedidos_status_descricao in ("Pedido Enviado","Entrega Realizada","Pago","Pedido Faturado")
and year(pedidos_dataEmissao) = "'.$ano.'"
and month(pedidos_dataEmissao) = "'.$mes.'"
group by Site,produto,CPF)itens_mes
left join (SELECT 
			pedidos_parceiroCanal as Site,
			pedidos_cliente_cpfCnpj as CPF,
			itens.produto as produto ,
			count(DISTINCT(pedidos_cliente_cpfCnpj)) as total
from pedidos
	inner join ( SELECT 
						itens.pedido_site as site,
						itens.pedido_numeroPedido,
						complemento.produto as produto
						from pedidos_itens itens
							inner join pedidos_itens_complemento complemento
							on itens.pedido_sku = complemento.sku 
						where year(pedido_dataEmissao) = "'.$ano.'"
						and month(pedido_dataEmissao) < "'.$mes.'") itens
				on pedidos.pedidos_numeroPedido = itens.pedido_numeroPedido
			where pedidos_status_descricao in ("Pedido Enviado","Entrega Realizada","Pago","Pedido Faturado")
			and year(pedidos_dataEmissao) = "'.$ano.'"
			and month(pedidos_dataEmissao) < "'.$mes.'"
			group by Site,produto,CPF) itens_antes
	on itens_mes.CPF = itens_antes.CPF and itens_mes.produto = itens_antes.produto
	group by itens_mes.Site,itens_mes.produto';


    $result = mysqli_query($mysqli, $sql);

    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    
        echo 'site:'. $row['Site'] . ' Produto:' . $row['Produto']  . ' Total:' . $row['Dentro_do_mes'] . 'clientes_recompra: ' . $row['Recorenci'] ."\n";

        $sql_e = 'INSERT INTO recorrencia_itens (
            site,
            produto,
            clientes_mes,
            clientes_recompra,
            recompra,
            mes,
            date_update
        )VALUES';
        
        $sql_e .= '("' .$row['Site'].'", 
        "'.$row['Produto'].'",
        "'.$row['Dentro_do_mes'].'",
        "'.$row['Recorenci'].'",
        "'.$row['Recorenci'] / $row['Dentro_do_mes'].'",
        "'.$mes.'",
        CURDATE())';
         
        mysqli_query($mysqli, $sql_e);

    }

?>
