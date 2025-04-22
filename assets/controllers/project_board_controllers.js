import {Controller} from "@hotwired/stimulus";
import { getComponent } from '@symfony/ux-live-component';

export default class ProjectBoardController extends Controller {
    async connect() {
        this.component = await getComponent(this.element);
        
        // Utiliser querySelectorAll au lieu de querySelector avec forEach
        document.querySelectorAll('.issue-container').forEach(issueContainer => {
            issueContainer.ondrop = (e) => {
                e.preventDefault();
                
                const id = e.dataTransfer.getData('text/plain');
                
                // S'assurer que l'ID et le status sont correctement transmis
                this.component.action('updateIssuesStatus', {
                    id: id, 
                    status: issueContainer.dataset.status
                });
                
                // Utiliser appendChild et non appendchild
                issueContainer.appendChild(document.getElementById(id));
            };
            
            issueContainer.ondragover = (e) => {
                e.preventDefault();
            };
        });
        
        // Utiliser querySelectorAll au lieu de querySelector avec forEach
        document.querySelectorAll('.issue-item').forEach(issueItem => {
            issueItem.addEventListener('dragstart', (e) => {
                e.dataTransfer.dropEffect = 'move';
                e.dataTransfer.setData('text/plain', e.target.id);
            });
        });
    }
}