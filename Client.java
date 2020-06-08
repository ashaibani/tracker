import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.net.InetAddress;
import java.net.Socket;

public class Client {
	private Socket socket;

	private Client(InetAddress serverAddress, int serverPort) throws Exception {
		this.socket = new Socket(serverAddress, serverPort);
	}

	private void start() throws IOException {
		String payload = "[3G*8800000015*00CD*AL,180916,064153,A,22.570512,N,113.8623267,E,0.00,154.8,0.0 ,11,100,100,0,0,00100018,7,0,460,1,9529,21809,155,9529,21242,132,9529,21405,131\r\n" + 
				"4" +
				",9529,63554,131,9529,63555,130,9529,63556,118,9529,21869,116,0,12.4]";
		try {
			PrintWriter out = new PrintWriter(this.socket.getOutputStream(), true);
			BufferedReader in = new BufferedReader(new InputStreamReader(this.socket.getInputStream()));
			out.println(payload);
			out.flush();
			System.out.println(in.readLine());
		} catch (Exception e) {
			e.printStackTrace();
		} finally {
			this.socket.close();
		}
	}

	public static void main(String[] args) throws Exception {
		Client client = new Client(InetAddress.getByName("95.217.215.19"), 1123);

		System.out.println("\r\nConnected to Server: " + client.socket.getInetAddress());
		client.start();
	}
}