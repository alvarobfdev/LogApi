@foreach($data as $numPedido=>$pedido)

El pedido EDI con nº <?=$numPedido?> del cliente <?=$pedido[0]["codcli"]?> contiene los siguientes artículos fallidos:<br>
    @foreach($pedido as $item)
        <?=$item["codart"]?>  <?=$item["descri"]?> x <?=$item["cantid"]?><br>
    @endforeach
@endforeach
