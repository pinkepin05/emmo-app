@if(request()->boolean('dilaporkan'))
    <input type="hidden" name="dilaporkan" value="1">
    <span class="badge text-bg-light">Sudah dikonfirmasi user</span>
@endif
