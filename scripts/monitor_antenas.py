import mysql.connector
import subprocess
import json

#cargar dinamicamente la base de datos en base a la que se cargue en PHP

def ping(ip):
    try:
        output = subprocess.check_output(['ping', '-c', '1', '-W', '1', ip])
        return True
    except subprocess.CalledProcessError:
        return False

def main():
    #se deben colocar las credenciales de la base de datos principal
    conn = mysql.connector.connect(
        host='172.168.30.252',
        user='spidernet',
        password='spidernet',
        database='doblenet'
    )
    cursor = conn.cursor(dictionary=True)

    # Asegúrate de tener un campo llamado 'nombre' en la tabla `clientes`
    cursor.execute("""
        SELECT id, nombre, ip_cliente
        FROM clientes
        WHERE ip_cliente IS NOT NULL AND ip_cliente != ''
    """)

    results = []

    for row in cursor.fetchall():
        estado = ping(row['ip_cliente'])
        results.append({
            'id': row['id'],
            'ip': row['ip_cliente'],
            'nombre': row['nombre'],
            'estado': 'Activo' if estado else 'Inactivo'
        })

    print(json.dumps(results, ensure_ascii=False))  # para soportar tildes y ñ

if __name__ == "__main__":
    main()
