import os
import sys
import json

from PIL import Image

def mapearPlanta(_pathPlanta = '', larguraExterna = 0, comprimentoExterno = 0):
    os.system('cls')

    if (larguraExterna > comprimentoExterno):
        aux = comprimentoExterno
        comprimentoExterno = larguraExterna
        larguraExterna = aux

    planta = {
        'larguraMetros': larguraExterna,
        'comprimentoMetros': comprimentoExterno
    }

    pathAbsolutoPlanta = os.path.abspath(os.path.join(os.path.dirname(__file__), _pathPlanta))
    ponteiroPlanta = Image.open(pathAbsolutoPlanta)
    
    if (ponteiroPlanta.width > ponteiroPlanta.height):
        ponteiroPlanta = ponteiroPlanta.rotate(90, Image.NEAREST, expand = 1)
    
    ponteiroPlanta.show()
    
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

    while (y <= plantaRGB.height - 1):
        x = 0
        
        while (x <= plantaRGB.width - 1):
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

    planta['areasInteresse'] = []
    planta['divisorias'] = []

    areaInteresse = 0
    divisoria = 0

    y = 0

    while (y <= comprimentoPlanta - 1):
        x = 0
        
        while (x <= larguraPlanta - 1):
            if (y == 0 or x == 0 or y == comprimentoPlanta - 1 or y == larguraPlanta - 1):
                mapeamentoPlanta[y][x] = 1

            if (x == 0 and areaInteresse != 0 and areaInteresse['comprimento'] > 0):
                x = areaInteresse['x']

            if (x == 0 and divisoria != 0 and divisoria['comprimento'] > 0):
                x = divisoria['x']

            indicadorAreaInterese = False
            indicadorDivisoria = False
            
            if (mapeamentoPlanta[y][x] == -2):
                indicadorAreaInterese = True
                mapeamentoPlanta[y][x] = 2

                if (areaInteresse == 0):
                    areaInteresse = {
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
            
            if (areaInteresse != 0 and areaInteresse['comprimento'] == 0):
                if (indicadorAreaInterese):
                    areaInteresse['largura'] += 1
                elif (areaInteresse['largura'] > 0):
                    areaInteresse['comprimento'] = 1
                    break
            elif (areaInteresse != 0 and areaInteresse['largura'] > 0):
                if (indicadorAreaInterese):
                    areaInteresse['comprimento'] += 1
                    break
                else:
                    for y in range(areaInteresse['y'], areaInteresse['y'] + areaInteresse['comprimento']):
                        mapeamentoPlanta[y][areaInteresse['x'] + areaInteresse['largura'] - 1] = 2
                    
                    for x in range(areaInteresse['x'], areaInteresse['x'] + areaInteresse['largura']):
                        mapeamentoPlanta[areaInteresse['y'] + areaInteresse['comprimento'] - 1][x] = 2

                    x = areaInteresse['x'] + areaInteresse['largura'] - 1
                    y = areaInteresse['y']

                    planta['areasInteresse'].append(areaInteresse)
                    print("Área de interesse encontrada: " + str(areaInteresse))

                    areaInteresse = 0
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
                    
                    for x in range(divisoria['x'], divisoria['x'] + divisoria['largura']):
                        mapeamentoPlanta[divisoria['y'] + divisoria['comprimento'] - 1][x] = 3

                    x = divisoria['x'] + divisoria['largura'] - 1
                    y = divisoria['y']

                    planta['divisorias'].append(divisoria)
                    print("Divisória encontrada: " + str(divisoria))

                    divisoria = 0
            
            x += 1
        y += 1

    planta['mapeamento'] = mapeamentoPlanta
    
    return planta

retornoMapeamento = mapearPlanta('../plantas/b-Way - Modelo de Apresentação.jpg', 7, 5)

print()
print(retornoMapeamento)
print()

##os.system('read -p \'Precione qualquer tecla para continuar...\' Saindo')
os.system('pause')