<?php

class LDAPIsPastellUser extends ActionExecutor
{
    public function go()
    {
        /** @var LDAPVerification $ldap */
        $ldap = $this->getMyConnecteur();
        $users = $ldap->getUserToCreate($this->objectInstancier->getInstance(Utilisateur::class));
        $result = "<table border='1'><tr>
						
					<th>login</th>
					<th>nom</th>
					<th>prenom</th>
					<th>email</th>
					<th>création ?</th>
					<th>synchronisation ?</th>
				</tr>";
        foreach ($users as $user) {
            $create = $user['create'] ? "oui" : "non";
            $synchronize = $user['synchronize'] ? "oui" : "non";
            $result .= "
			<tr>
				<td>{$user['login']}</td>
				<td>{$user['nom']}</td>
				<td>{$user['prenom']}</td>
				<td>{$user['email']}</td>
				<td>$create</td>
				<td>$synchronize</td>
			</tr>";
        }
        $result .= "</table>";
        $this->setLastMessage($result);
        return true;
    }
}
