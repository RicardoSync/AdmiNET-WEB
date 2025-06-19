import sys
import mysql.connector
import paramiko
import json
import re
from collections import defaultdict

def conectar_mikrotik(ip, user, password, port):
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(ip, username=user, password=password, port=int(port), timeout=5)
    return ssh

def ping_desde_mikrotik(ssh, ip):
    stdin, stdout, stderr = ssh.exec_command(f'/ping {ip} count=2 interval=0.5s')
    salida = stdout.read().decode()
    match = re.search(r'packet-loss=(\d+)%', salida)
    if match:
        perdida = int(match.group(1))
        return perdida < 100
    return False

def main():
    # ⚠️ Obtener el nombre de la base de datos desde argumentos
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No se proporcionó el nombre de la base de datos"}))
        return

    db_name = sys.argv[1]

    conn = mysql.connector.connect(
        host='172.168.30.252',
        user='spidernet',
        password='spidernet',
        database=db_name
    )
    cursor = conn.cursor(dictionary=True)

    cursor.execute("""
        SELECT c.id, c.nombre, c.ip_cliente, c.id_microtik,
               m.ip AS mk_ip, m.username, m.password, m.port
        FROM clientes c
        JOIN credenciales_microtik m ON c.id_microtik = m.id
        WHERE c.ip_cliente IS NOT NULL AND c.ip_cliente != ''
    """)
    clientes = cursor.fetchall()

    grupos = defaultdict(list)
    for cli in clientes:
        grupos[cli['id_microtik']].append(cli)

    resultados = []

    for id_mk, grupo in grupos.items():
        mk = grupo[0]
        try:
            ssh = conectar_mikrotik(mk['mk_ip'], mk['username'], mk['password'], mk['port'])
        except Exception:
            for c in grupo:
                resultados.append({
                    'id': c['id'],
                    'ip': c['ip_cliente'],
                    'nombre': c['nombre'],
                    'estado': 'Inactivo'
                })
            continue

        for c in grupo:
            try:
                activo = ping_desde_mikrotik(ssh, c['ip_cliente'])
            except:
                activo = False
            resultados.append({
                'id': c['id'],
                'ip': c['ip_cliente'],
                'nombre': c['nombre'],
                'estado': 'Activo' if activo else 'Inactivo'
            })

        ssh.close()

    print(json.dumps(resultados, ensure_ascii=False))

if __name__ == "__main__":
    main()
