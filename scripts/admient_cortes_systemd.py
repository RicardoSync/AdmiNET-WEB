#!/usr/bin/env python3
import mysql.connector
import time
from datetime import datetime
import paramiko
import requests
import urllib.parse

"""
Funciona cargando de manera dinamica las bases de datos, de esta manera tenemos un solo script
un solo servicio
30/may/2025

"""
def registrar_log_global(conexion, id_cliente, ip_mikrotik, accion, resultado, mensaje):
    try:
        cursor = conexion.cursor()
        cursor.execute("""
            INSERT INTO logs_acciones_red (id_cliente, ip_mikrotik, accion, resultado, mensaje)
            VALUES (%s, %s, %s, %s, %s)
        """, (id_cliente, ip_mikrotik, accion, resultado, mensaje))
        conexion.commit()
        cursor.close()
    except Exception as e:
        print(f"⚠️ No se pudo registrar el log: {e}")

def bloquear_cliente_address_list(credenciales, ip_cliente, nombre_cliente, id_cliente, conexion):
    username, password, host = credenciales
    try:
        cliente_ssh = paramiko.SSHClient()
        cliente_ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        cliente_ssh.connect(host, username=username, password=password, timeout=5)

        comentario_address_list = f'"{nombre_cliente}"'
        comentario_queue = f'"{nombre_cliente} - Cliente bloqueado automáticamente"'
        comandos = [
            f'/ip/firewall/address-list/add list=corte address={ip_cliente} comment={comentario_address_list}',
            f'/queue/simple/set comment={comentario_queue} [find where target={ip_cliente}/32]'
        ]
        for comando in comandos:
            stdin, stdout, stderr = cliente_ssh.exec_command(comando)
            errores = stderr.read().decode()
            if errores:
                print(f"⚠️ Error al ejecutar: {comando}\n{errores}")
        cliente_ssh.close()
        return True
    except Exception as e:
        print(f"❌ Error SSH al bloquear {nombre_cliente}: {e}")
        registrar_log_global(conexion, id_cliente, host, 'bloqueo', 'fallo', f'Error SSH: {e}')
        return False

def desbloquear_cliente_address_list(credenciales, ip_cliente, nombre_cliente, id_cliente, conexion):
    username, password, host = credenciales
    try:
        cliente_ssh = paramiko.SSHClient()
        cliente_ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        cliente_ssh.connect(host, username=username, password=password, timeout=5)
        comandos = [
            f'/ip/firewall/address-list/remove [find address={ip_cliente} list=corte]',
            f'/queue/simple/set comment="" [find where target={ip_cliente}/32]'
        ]
        for comando in comandos:
            stdin, stdout, stderr = cliente_ssh.exec_command(comando)
            errores = stderr.read().decode()
            if errores:
                print(f"⚠️ Error al ejecutar: {comando}\n{errores}")
        cliente_ssh.close()
        return True
    except Exception as e:
        print(f"❌ Error SSH al desbloquear {nombre_cliente}: {e}")
        registrar_log_global(conexion, id_cliente, host, 'desbloqueo', 'fallo', f'Error SSH: {e}')
        return False

def verificar_address_list(credenciales, ip_cliente):
    username, password, host = credenciales
    try:
        cliente_ssh = paramiko.SSHClient()
        cliente_ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        cliente_ssh.connect(host, username=username, password=password)
        comando = f'/ip/firewall/address-list/print where address={ip_cliente} list=corte'
        stdin, stdout, stderr = cliente_ssh.exec_command(comando)
        salida = stdout.read().decode()
        cliente_ssh.close()
        return bool(salida.strip())
    except Exception as e:
        print(f"❌ Error al verificar address-list: {e}")
        return False

def enviar_mensaje_whatsapp(numero, apikey, nombre, empresa, direccion, telefono_empresa):
    mensaje = f"👋 ¡Hola {nombre}!\n\n🚫 Tu servicio ha sido *suspendido automáticamente* por el sistema de *{empresa}*.\n\n📍 Dirección registrada: {direccion}\n\n⚠️ Este es un mensaje automático enviado por nuestro 🤖 bot.\n❗ *Por favor no respondas a este mensaje.*\n📞 Contacto: *{telefono_empresa}*\n\nGracias por tu comprensión 🙏"
    texto = urllib.parse.quote(mensaje)
    url = f"https://api.callmebot.com/whatsapp.php?phone={numero}&text={texto}&apikey={apikey}"
    try:
        r = requests.get(url)
        if r.status_code == 200:
            print(f"✅ WhatsApp enviado a {nombre}")
        else:
            print(f"⚠️ Error al enviar WhatsApp a {nombre}: {r.status_code}")
    except Exception as e:
        print(f"❌ Excepción al enviar WhatsApp: {e}")

def ejecutar_corte_individual(base_datos):
    try:
        conexion = mysql.connector.connect(
            host="softwarescobedo.com.mx",
            user="adminet",
            password="MinuzaFea265/",
            database=base_datos
        )
        cursor = conexion.cursor(dictionary=True)

        cursor.execute("""
            SELECT c.id, c.nombre, c.ip_cliente, c.direccion, c.telefono, c.id_microtik,
                m.username, m.password, m.ip AS ip_mikrotik,
                a.apikey, d.nombreWisp, d.telefono AS telefono_empresa
            FROM clientes c
            LEFT JOIN (
                SELECT id_cliente, MAX(proximo_pago) AS ultimo_pago
                FROM pagos
                GROUP BY id_cliente
            ) p ON c.id = p.id_cliente
            JOIN credenciales_microtik m ON c.id_microtik = m.id
            LEFT JOIN clientes_apikeys a ON c.id = a.id_cliente AND a.activo = 1
            LEFT JOIN datosEmpresa d ON d.id = 1
            WHERE
                (p.ultimo_pago IS NULL OR DATE(p.ultimo_pago) < CURDATE())
                AND c.estado = 'Activo'
                AND c.dia_corte = DAY(CURDATE())
        """)
        clientes = cursor.fetchall()

        for cliente in clientes:
            credenciales = (cliente['username'], cliente['password'], cliente['ip_mikrotik'])
            if not verificar_address_list(credenciales, cliente['ip_cliente']):
                exito = bloquear_cliente_address_list(credenciales, cliente['ip_cliente'], cliente['nombre'], cliente['id'], conexion)
                if exito:
                    cursor_update = conexion.cursor()
                    cursor_update.execute("UPDATE clientes SET estado = 'Bloqueado' WHERE id = %s", (cliente['id'],))
                    conexion.commit()
                    cursor_update.close()
                    if cliente['apikey']:
                        enviar_mensaje_whatsapp(
                            numero=cliente['telefono'],
                            apikey=cliente['apikey'],
                            nombre=cliente['nombre'],
                            empresa=cliente['nombreWisp'],
                            direccion=cliente['direccion'],
                            telefono_empresa=cliente['telefono_empresa']
                        )
                    registrar_log_global(conexion, cliente['id'], cliente['ip_mikrotik'], 'bloqueo', 'exito', 'Cliente bloqueado automáticamente por systemd')
            else:
                registrar_log_global(conexion, cliente['id'], cliente['ip_mikrotik'], 'bloqueo', 'omitido', 'Cliente ya estaba en address list')

        cursor.execute("""
            SELECT c.id, c.nombre, c.ip_cliente, c.direccion, c.telefono, c.id_microtik,
                m.username, m.password, m.ip AS ip_mikrotik,
                a.apikey, d.nombreWisp, d.telefono AS telefono_empresa
            FROM clientes c
            JOIN pagos p ON c.id = p.id_cliente
            JOIN credenciales_microtik m ON c.id_microtik = m.id
            LEFT JOIN clientes_apikeys a ON c.id = a.id_cliente AND a.activo = 1
            LEFT JOIN datosEmpresa d ON d.id = 1
            WHERE DATE(p.proximo_pago) >= CURDATE()
                AND c.estado = 'Bloqueado'
                AND NOT ISNULL(c.ip_cliente)
            GROUP BY c.id, c.nombre, c.ip_cliente, c.direccion, c.telefono, c.id_microtik,
                    m.username, m.password, m.ip, a.apikey, d.nombreWisp, d.telefono
        """)
        clientes_activar = cursor.fetchall()

        for cliente in clientes_activar:
            credenciales = (cliente['username'], cliente['password'], cliente['ip_mikrotik'])
            if verificar_address_list(credenciales, cliente['ip_cliente']):
                exito = desbloquear_cliente_address_list(credenciales, cliente['ip_cliente'], cliente['nombre'], cliente['id'], conexion)
                if exito:
                    cursor_update = conexion.cursor()
                    cursor_update.execute("UPDATE clientes SET estado = 'Activo' WHERE id = %s", (cliente['id'],))
                    conexion.commit()
                    cursor_update.close()
                    if cliente['apikey']:
                        enviar_mensaje_whatsapp(
                            numero=cliente['telefono'],
                            apikey=cliente['apikey'],
                            nombre=cliente['nombre'],
                            empresa=cliente['nombreWisp'],
                            direccion=cliente['direccion'],
                            telefono_empresa=cliente['telefono_empresa']
                        )
                    registrar_log_global(conexion, cliente['id'], cliente['ip_mikrotik'], 'desbloqueo', 'exito', 'Cliente desbloqueado automáticamente por systemd')

        cursor.close()
        conexion.close()

    except Exception as e:
        print(f"❌ Error en base de datos '{base_datos}': {e}")

if __name__ == "__main__":
    print("🚀 Script de cortes automáticos multibase iniciado.")
    while True:
        try:
            conexion_global = mysql.connector.connect(
                host="softwarescobedo.com.mx",
                user="adminet",
                password="MinuzaFea265/",
                database="adminet_global"
            )
            cursor_global = conexion_global.cursor(dictionary=True)
            cursor_global.execute("SELECT base_datos FROM usuarios_empresas")
            bases = cursor_global.fetchall()
            cursor_global.close()
            conexion_global.close()

            for entrada in bases:
                base_actual = entrada['base_datos']
                print(f"🛠 Procesando base: {base_actual}")
                ejecutar_corte_individual(base_actual)

        except Exception as e:
            print(f"❌ Error al conectar a adminet_global: {e}")

        for i in range(90, 0, -1):
            print(f"⏳ Siguiente ciclo en {i} segundos...", end="\r", flush=True)
            time.sleep(1)
        print("\n")
