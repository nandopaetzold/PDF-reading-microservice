## Micro Serviço para leitura de PDF - ADAPTADO PARA O PROJETO PORTUARIO

----------------------------------------------------------------------------

Este micro serviço tem como objetivo ler um arquivo PDF e retornar o texto especifico do mesmo.

## Exemplo de retornar o texto do PDF.



## Endpoint: http://localhost
## Metodo: POST
## Header: [
    "Content-Type: application/json",
    "Authorization: Token: {token}"
]

## Body: 
```json
{
    "data": "base64"
}
```

```json
{
    "placas": {
        "0": "ACT0E--",
        "3": "BTA5B--"
    },
    "mic": "24PY5122--X",
    "conhecimento_carga": "PY193104---",
    "peso_total": 31640
}
```
## Algumas informações foram omitidas por questões de segurança.


@autor: Fernando dos Santos Paetzold


