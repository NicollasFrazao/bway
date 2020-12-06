import os
import sys
import json
import mysql.connector

from PIL import Image
from flask import Flask

app = Flask(__name__)

@app.route("/gerarRota/<_codigoEstabelecimento>/<_codigoSetorOrigem>/<_codigoSetorDestino>")
def gerarRota(_codigoEstabelecimento, _codigoSetorOrigem, _codigoSetorDestino):
    #os.system('cls')

    rota = []
    
    pathMapeamento = os.path.abspath(os.path.join(os.path.dirname(__file__), '../../../storage/app/public/estabelecimentos/mapeamentos/' + str(_codigoEstabelecimento) + '.json'))
    pathRota = os.path.abspath(os.path.join(os.path.dirname(__file__), '../../../storage/app/public/estabelecimentos/rotas/' + str(_codigoEstabelecimento) + '-' + str(_codigoSetorOrigem) + '-' + str(_codigoSetorDestino) + '.json'))

    conexao = mysql.connector.connect(
        host = 'localhost',
        user = 'root',
        password = 'root',
        database = 'db_bway'
    )

    query = conexao.cursor(dictionary=True)

    query.execute(
        """
            select
                *
            from
                tb_rota
            where
                cd_estabelecimento = %s
                and cd_origem = %s
                and cd_destino = %s
        """,
        (_codigoEstabelecimento, _codigoSetorOrigem, _codigoSetorDestino)
    )

    result = query.fetchall()

    if (len(result) > 0):
        with open(pathRota) as ponteiro:
            rota = json.load(ponteiro)
    elif (os.path.isfile(pathRota)):
        query = conexao.cursor()
        query.execute(
            """
                insert into
                    tb_rota
                    (
                        cd_estabelecimento,
                        cd_origem,
                        cd_destino,
                        created_at
                    )
                values
                    (
                        %s,
                        %s,
                        %s,
                        now()
                    )
            """,
            (_codigoEstabelecimento, _codigoSetorOrigem, _codigoSetorDestino)
        )
        conexao.commit()

        with open(pathRota) as ponteiro:
            rota = json.load(ponteiro)
    else:
        with open(pathMapeamento) as ponteiro:
            mapeamentoEstabelecimento = json.load(ponteiro)['ds_mapeamento']  
            
            query = conexao.cursor(dictionary=True)

            query.execute("""
                select
                    *
                from
                    tb_setor
                where
                    cd_setor = %s
            """, (_codigoSetorOrigem,))
            
            result = query.fetchall()[0]

            setorOrigem = result

            query = conexao.cursor(dictionary=True)

            query.execute("""
                select
                    *
                from
                    tb_setor
                where
                    cd_setor = %s
            """, (_codigoSetorDestino,))
            
            result = query.fetchall()[0]

            setorDestino = result

            _setorOrigem = {
                'cd_setor': setorOrigem['cd_setor'],
                'nm_setor': setorOrigem['nm_setor'],
                'vl_x': int(setorOrigem['vl_x'] + (setorOrigem['vl_largura']/2)),
                'vl_y': int(setorOrigem['vl_y'] + (setorOrigem['vl_comprimento']/2))
            }

            _setorDestino = {
                'cd_setor': setorDestino['cd_setor'],
                'nm_setor': setorDestino['nm_setor'],
                'vl_x': int(setorDestino['vl_x'] + (setorDestino['vl_largura']/2)),
                'vl_y': int(setorDestino['vl_y'] + (setorDestino['vl_comprimento']/2))
            }

            print(str(_codigoEstabelecimento) + ': ' + str(_setorOrigem) + ' => ' + str(_setorDestino))
            print()

            posicaoAtual = {
                'vl_x': _setorOrigem['vl_x'],
                'vl_y': _setorOrigem['vl_y'],
                'parent': ''
            }

            rota = []

            listaAberta = {}
            listaFechada = {}

            while (posicaoAtual['vl_x'] != _setorDestino['vl_x'] or posicaoAtual['vl_y'] != _setorDestino['vl_y']):
                vizinhos = []

                posicao = posicaoAtual.copy()
                index = (str(posicao['vl_x']) + 'x' + str(posicao['vl_y']))

                try:
                    posicaoExiste = mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']]
                    posicaoExiste = True
                except:
                    posicaoExiste = False

                if (posicaoExiste):
                    if (mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']] == 1):
                        x = posicao['vl_x']
                        y = posicao['vl_y']

                        if (x != 0 and x != len(mapeamentoEstabelecimento[y]) - 1 and y != 0 and y != len(mapeamentoEstabelecimento) - 1):
                            mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']] = 0

                    if (
                        mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']] not in (1, 3)
                        and index not in listaFechada
                        and index not in listaAberta
                    ):
                        listaAberta = inserirListaAberta(listaAberta, posicao, _setorOrigem, _setorDestino)

                posicao = posicaoAtual.copy()
                posicao['vl_x'] += 1
                posicao['parent'] = (str(posicaoAtual['vl_x']) + 'x' + str(posicaoAtual['vl_y']))

                index = (str(posicao['vl_x']) + 'x' + str(posicao['vl_y']))

                try:
                    posicaoExiste = mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']]
                    posicaoExiste = True
                except:
                    posicaoExiste = False

                if (posicaoExiste):
                    if (mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']] == 1):
                        x = posicao['vl_x']
                        y = posicao['vl_y']

                        if (x != 0 and x != len(mapeamentoEstabelecimento[y]) - 1 and y != 0 and y != len(mapeamentoEstabelecimento) - 1):
                            mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']] = 0
                    
                    if (
                        mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']] not in (1, 3)
                        and index not in listaFechada
                        and index not in listaAberta
                    ):
                        aux = False

                        for cont in range(posicao['vl_x'], posicao['vl_x'] + 20):
                            try:
                                if mapeamentoEstabelecimento[posicao['vl_y']][cont] in (1, 3):
                                    aux = True
                                    break
                            except:
                                pass
                        
                        if not aux:
                            listaAberta = inserirListaAberta(listaAberta, posicao, _setorOrigem, _setorDestino)
                            vizinhos.append(listaAberta[index].copy())

                posicao = posicaoAtual.copy()
                posicao['vl_x'] -= 1
                posicao['parent'] = (str(posicaoAtual['vl_x']) + 'x' + str(posicaoAtual['vl_y']))

                index = (str(posicao['vl_x']) + 'x' + str(posicao['vl_y']))

                try:
                    posicaoExiste = mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']]
                    posicaoExiste = True
                except:
                    posicaoExiste = False

                if (posicaoExiste):
                    if (mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']] == 1):
                        x = posicao['vl_x']
                        y = posicao['vl_y']

                        if (x != 0 and x != len(mapeamentoEstabelecimento[y]) - 1 and y != 0 and y != len(mapeamentoEstabelecimento) - 1):
                            mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']] = 0
                    
                    if (
                        mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']] not in (1, 3)
                        and index not in listaFechada
                        and index not in listaAberta
                    ):
                        aux = False

                        for cont in range(posicao['vl_x'] - 20, posicao['vl_x']):
                            try:
                                if mapeamentoEstabelecimento[posicao['vl_y']][cont] in (1, 3):
                                    aux = True
                                    break
                            except:
                                pass
                        
                        if not aux:
                            listaAberta = inserirListaAberta(listaAberta, posicao, _setorOrigem, _setorDestino)
                            vizinhos.append(listaAberta[index].copy())

                posicao = posicaoAtual.copy()
                posicao['vl_y'] += 1
                posicao['parent'] = (str(posicaoAtual['vl_x']) + 'x' + str(posicaoAtual['vl_y']))

                index = (str(posicao['vl_x']) + 'x' + str(posicao['vl_y']))

                try:
                    posicaoExiste = mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']]
                    posicaoExiste = True
                except:
                    posicaoExiste = False

                if (posicaoExiste):
                    if (mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']] == 1):
                        x = posicao['vl_x']
                        y = posicao['vl_y']

                        if (x != 0 and x != len(mapeamentoEstabelecimento[y]) - 1 and y != 0 and y != len(mapeamentoEstabelecimento) - 1):
                            mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']] = 0
                    
                    if (
                        mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']] not in (1, 3)
                        and index not in listaFechada
                        and index not in listaAberta
                    ):
                        aux = False

                        for cont in range(posicao['vl_y'], posicao['vl_y'] + 20):
                            try:
                                if mapeamentoEstabelecimento[cont][posicao['vl_x']] in (1, 3):
                                    aux = True
                                    break
                            except:
                                pass
                        
                        if not aux:
                            listaAberta = inserirListaAberta(listaAberta, posicao, _setorOrigem, _setorDestino)
                            vizinhos.append(listaAberta[index].copy())

                posicao = posicaoAtual.copy()
                posicao['vl_y'] -= 1
                posicao['parent'] = (str(posicaoAtual['vl_x']) + 'x' + str(posicaoAtual['vl_y']))

                index = (str(posicao['vl_x']) + 'x' + str(posicao['vl_y']))

                try:
                    posicaoExiste = mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']]
                    posicaoExiste = True
                except:
                    posicaoExiste = False

                if (posicaoExiste):
                    if (mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']] == 1):
                        x = posicao['vl_x']
                        y = posicao['vl_y']

                        if (x != 0 and x != len(mapeamentoEstabelecimento[y]) - 1 and y != 0 and y != len(mapeamentoEstabelecimento) - 1):
                            mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']] = 0
                    
                    if (
                        mapeamentoEstabelecimento[posicao['vl_y']][posicao['vl_x']] not in (1, 3)
                        and index not in listaFechada
                        and index not in listaAberta
                    ):
                        aux = False

                        for cont in range(posicao['vl_y'] - 20, posicao['vl_y']):
                            try:
                                if mapeamentoEstabelecimento[cont][posicao['vl_x']] in (1, 3):
                                    aux = True
                                    break
                            except:
                                pass

                        if not aux:
                            listaAberta = inserirListaAberta(listaAberta, posicao, _setorOrigem, _setorDestino)
                            vizinhos.append(listaAberta[index].copy())
                
                posicao = posicaoAtual.copy()
                index = (str(posicao['vl_x']) + 'x' + str(posicao['vl_y']))

                if (index not in listaFechada and index in listaAberta):
                    listaFechada.update(
                        {
                            index: listaAberta[index].copy()
                        }
                    )

                    listaAberta.pop(index)

                vizinhos = sorted(vizinhos, key=lambda x: (x['F'], abs(x['vl_x'] - _setorDestino['vl_x']) + abs(x['vl_y'] - _setorDestino['vl_y'])))
                aux = sorted(listaAberta.items(), key=lambda x: (x[1]['F'], abs(x[1]['vl_x'] - _setorDestino['vl_x']) + abs(x[1]['vl_y'] - _setorDestino['vl_y'])))
                listaAberta = dict(aux)
                
                if (len(listaAberta) > 0):
                    posicaoAtual['vl_x'] = aux[0][1]['vl_x']
                    posicaoAtual['vl_y'] = aux[0][1]['vl_y']
                    posicaoAtual['parent'] = aux[0][1]['parent']
                else:
                    break
            
            if (posicaoAtual['vl_x'] == _setorDestino['vl_x'] and posicaoAtual['vl_y'] == _setorDestino['vl_y']):
                while (posicaoAtual['parent'] != ''):
                    rota.append(posicaoAtual)
                    posicaoAtual = listaFechada[posicaoAtual['parent']].copy()
                
                rota.append(posicaoAtual)

            with open(pathRota, "w") as ponteiroRota:
                json.dump(rota, ponteiroRota)

                query = conexao.cursor()
                query.execute(
                    """
                        insert into
                            tb_rota
                            (
                                cd_estabelecimento,
                                cd_origem,
                                cd_destino,
                                created_at
                            )
                        values
                            (
                                %s,
                                %s,
                                %s,
                                now()
                            )
                    """,
                    (_codigoEstabelecimento, _codigoSetorOrigem, _codigoSetorDestino)
                )
                conexao.commit()  
            
    return json.dumps(rota)

def inserirListaAberta(_listaAberta, _posicao, _setorOrigem, _setorDestino):
    index = (str(_posicao['vl_x']) + 'x' + str(_posicao['vl_y']))

    G = 0
    H = 0

    G = abs(_posicao['vl_x'] - _setorOrigem['vl_x']) + abs(_posicao['vl_y'] - _setorOrigem['vl_y'])
    H = abs(_posicao['vl_x'] - _setorDestino['vl_x']) + abs(_posicao['vl_y'] - _setorDestino['vl_y'])
    
    if (index not in _listaAberta):
        _listaAberta.update(
            {
                index: {
                    'vl_x': _posicao['vl_x'],
                    'vl_y': _posicao['vl_y'],
                    'parent': _posicao['parent'],
                    'F': G + H
                }
            }
        )

    return _listaAberta

@app.route("/mapearPlanta")
def mapearPlanta(larguraExterna = 5, comprimentoExterno = 7, _pathPlanta = '../../../storage/app/public/estabelecimentos/plantas/estabelecimento.jpg'):
    # Definição do Mapeamento
    # 0 - Chão Livre
    # 1 - Parede da Extreminadade
    # 2 - Setor
    # 3 - Divisoria

    os.system('cls')

    if (larguraExterna > comprimentoExterno):
        aux = comprimentoExterno
        comprimentoExterno = larguraExterna
        larguraExterna = aux

    planta = {
        'larguraMetros': larguraExterna,
        'comprimentoMetros': comprimentoExterno
    }

    pathMapeamento = os.path.abspath(os.path.join(os.path.dirname(__file__), '../../../storage/app/public/estabelecimentos/mapeamentos/mapeamento.json'))

    pathAbsolutoPlanta = os.path.abspath(os.path.join(os.path.dirname(__file__), _pathPlanta))
    ponteiroPlanta = Image.open(pathAbsolutoPlanta)
    
    if (ponteiroPlanta.width > ponteiroPlanta.height):
        ponteiroPlanta = ponteiroPlanta.rotate(90, Image.NEAREST, expand = 1)
    
    #ponteiroPlanta.show()
    
    plantaRGB = ponteiroPlanta.convert('RGB')

    larguraPlanta = 0
    comprimentoPlanta = 0

    os.system('cls')
    print('Mapeando extremidade da planta...')
    print()

    indicadorAreaMapeada = False

    xFinal = False
    xInicial = False

    y = 0

    for y in range(plantaRGB.height):
        x = 0
        
        for x in range(plantaRGB.width):
            if (x == 0 and comprimentoPlanta > 0):
                x = xFinal

            RGB = plantaRGB.getpixel((x,y))
            R, G, B = RGB

            indicadorParede = False

            if (R > 100):
                if (G < 100 and B < 100):
                    indicadorParede = True

                    #print('%dx%d => (%d, %d, %d)' % (x, y, R, G, B))
            
            if (comprimentoPlanta == 0):
                if (indicadorParede):
                    if (larguraPlanta == 0):
                        xInicial = x
                        yInicial = y

                    larguraPlanta = larguraPlanta + 1

                    if (x == plantaRGB.width):
                        comprimentoPlanta = 1
                        xFinal = x - 1
                elif (larguraPlanta > 0):
                    comprimentoPlanta = 1
                    xFinal = x - 1
                    break
            elif (comprimentoPlanta > 0):
                if (indicadorParede):
                    comprimentoPlanta = comprimentoPlanta + 1

                    break
                else:
                    indicadorAreaMapeada = True
                    break
            
            x += 1

        if (indicadorAreaMapeada):
            break
        
        y += 1

    planta['larguraPixel'] = larguraPlanta
    planta['comprimentoPixel'] = comprimentoPlanta         
    
    mapeamentoPlanta = [ [ 0 for x in range(larguraPlanta) ] for y in range(comprimentoPlanta) ]
    mapeamentoImagem = [ [ 0 for x in range(plantaRGB.width) ] for y in range(plantaRGB.height) ]

    os.system('cls')
    print('Buscando divisórias e áreas de interesse da planta...')
    print()

    for y in range(comprimentoPlanta):
        for x in range(larguraPlanta):
            if (y == 0 or x == 0 or y == comprimentoPlanta - 1 or y == larguraPlanta - 1):
                mapeamentoImagem[yInicial + y][xInicial + x] = -1

            RGB = plantaRGB.getpixel((xInicial + x, yInicial + y))
            R, G, B = RGB

            if (G != 0 and R/G < 0.85 and B/G < 0.85):
                mapeamentoPlanta[y][x] = -3
                mapeamentoImagem[yInicial + y][xInicial + x] = 3
                    
                #print('%dx%d => (%d, %d, %d)' % (x, y, R, G, B))
            elif (B != 0 and G/B < 0.50 and R/B < 0.50):
                mapeamentoPlanta[y][x] = -2
                mapeamentoImagem[yInicial + y][xInicial + x] = 2
                
                #print('%dx%d => (%d, %d, %d)' % (x, y, R, G, B))

    os.system('cls')
    print('Mapeando divisórias e áreas de interesse da planta...')
    print()

    planta['setores'] = []
    planta['divisorias'] = []

    setores = 0
    divisoria = 0

    y = 0

    while (y <= comprimentoPlanta - 1):
        x = 0
        
        while (x <= larguraPlanta - 1):
            if (y == 0 or x == 0 or y == comprimentoPlanta - 1 or y == larguraPlanta - 1):
                mapeamentoPlanta[y][x] = 1

            if (x == 0 and setores != 0 and setores['comprimento'] > 0):
                x = setores['x']

            if (x == 0 and divisoria != 0 and divisoria['comprimento'] > 0):
                x = divisoria['x']

            indicadorAreaInterese = False
            indicadorDivisoria = False
            
            if (mapeamentoPlanta[y][x] == -2):
                indicadorAreaInterese = True
                mapeamentoPlanta[y][x] = 2

                if (setores == 0):
                    setores = {
                        'x': x,
                        'y': y,
                        'largura': 0,
                        'comprimento': 0
                    }
            elif (mapeamentoPlanta[y][x] == -3):
                indicadorDivisoria = True
                mapeamentoPlanta[y][x] = 3

                if (divisoria == 0):
                    divisoria = {
                        'x': x,
                        'y': y,
                        'largura': 0,
                        'comprimento': 0
                    }
            
            if (setores != 0 and setores['comprimento'] == 0):
                if (indicadorAreaInterese):
                    setores['largura'] += 1
                elif (setores['largura'] > 0):
                    setores['comprimento'] = 1
                    break
            elif (setores != 0 and setores['largura'] > 0):
                if (indicadorAreaInterese):
                    setores['comprimento'] += 1
                    break
                else:
                    for y in range(setores['y'], setores['y'] + setores['comprimento']):
                        mapeamentoPlanta[y][setores['x'] + setores['largura'] - 1] = 2
                    
                    for x in range(setores['x'], setores['x'] + setores['largura']):
                        mapeamentoPlanta[setores['y'] + setores['comprimento'] - 1][x] = 2

                    x = setores['x'] + setores['largura'] - 1
                    y = setores['y']

                    planta['setores'].append(setores)
                    print("Setor encontrado: " + str(setores))

                    setores = 0
            elif (divisoria != 0 and divisoria['comprimento'] == 0):
                if (indicadorDivisoria):
                    divisoria['largura'] += 1
                elif (divisoria['largura'] > 0):
                    divisoria['comprimento'] = 1
                    break
            elif (divisoria != 0 and divisoria['largura'] > 0):
                if (indicadorDivisoria):
                    divisoria['comprimento'] += 1
                    break
                else:
                    for y in range(divisoria['y'], divisoria['y'] + divisoria['comprimento']):
                        mapeamentoPlanta[y][divisoria['x'] + divisoria['largura'] - 1] = 3

                    for y in range(divisoria['y'], divisoria['y'] + divisoria['comprimento']):
                        mapeamentoPlanta[y][divisoria['x']] = 3
                    
                    for x in range(divisoria['x'], divisoria['x'] + divisoria['largura']):
                        mapeamentoPlanta[divisoria['y'] + divisoria['comprimento'] - 1][x] = 3

                    for x in range(divisoria['x'], divisoria['x'] + divisoria['largura']):
                        mapeamentoPlanta[divisoria['y']][x] = 3

                    x = divisoria['x'] + divisoria['largura'] - 1
                    y = divisoria['y']

                    planta['divisorias'].append(divisoria)
                    print("Divisória encontrada: " + str(divisoria))

                    divisoria = 0
            
            x += 1
        y += 1

    planta['ds_mapeamento'] = mapeamentoPlanta

    with open(pathMapeamento, "w") as ponteiroMapeamento:
        json.dump(planta, ponteiroMapeamento)
    
    return json.dumps(planta)

if __name__ == "__main__":
    #app.run()
    #retornoMapeamento = mapearPlanta()
    gerarRota(1, 11, 10)

#gerarRota(1, 1, [2, 7, 11, 10, 8, 4])
#gerarRota(1, 1, 2)

#os.system('read -p \'Precione qualquer tecla para continuar...\' Saindo')
#os.system('pause')