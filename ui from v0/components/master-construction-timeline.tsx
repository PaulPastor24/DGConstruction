'use client'

import { makeStyles } from '@fluentui/react-components'
import { useState } from 'react'

const useStyles = makeStyles({
  root: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '40px 32px',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
    border: '1px solid rgba(0, 0, 0, 0.08)',
  },
  title: {
    fontSize: '20px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '6px',
  },
  subtitle: {
    fontSize: '14px',
    color: '#999999',
    fontWeight: 400,
    marginBottom: '32px',
  },
  timeline: {
    display: 'flex',
    alignItems: 'center',
    gap: '0',
    overflowX: 'auto',
    paddingBottom: '16px',
  },
  phaseContainer: {
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    minWidth: 'fit-content',
    position: 'relative',
  },
  phaseConnector: {
    width: '32px',
    height: '3px',
    backgroundColor: '#EEEEEA',
    margin: '0 -3px',
    position: 'relative',
    zIndex: 0,
  },
  phaseConnectorCompleted: {
    backgroundColor: '#66BB6A',
  },
  phaseNode: {
    width: '60px',
    height: '60px',
    borderRadius: '50%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#EEEEEA',
    border: '2px solid #DDDDDD',
    fontSize: '24px',
    fontWeight: 700,
    cursor: 'pointer',
    transition: 'all 0.3s ease',
    position: 'relative',
    zIndex: 1,
    '&:hover': {
      transform: 'scale(1.1)',
      boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
    },
  },
  phaseNodeCompleted: {
    backgroundColor: '#66BB6A',
    border: '2px solid #2F6B3C',
    color: '#FFFFFF',
  },
  phaseNodeCurrent: {
    backgroundColor: '#2F6B3C',
    border: '2px solid #2F6B3C',
    color: '#FFFFFF',
    width: '72px',
    height: '72px',
    boxShadow: '0 4px 16px rgba(47, 107, 60, 0.3)',
  },
  phaseLabel: {
    marginTop: '16px',
    textAlign: 'center',
    fontSize: '13px',
    fontWeight: 600,
    color: '#333333',
    width: '80px',
  },
  phaseStatus: {
    fontSize: '11px',
    color: '#999999',
    fontWeight: 500,
    marginTop: '4px',
  },
  tooltip: {
    position: 'absolute',
    bottom: 'calc(100% + 16px)',
    left: '50%',
    transform: 'translateX(-50%)',
    backgroundColor: '#2F6B3C',
    color: '#FFFFFF',
    padding: '12px 16px',
    borderRadius: '10px',
    fontSize: '12px',
    fontWeight: 500,
    whiteSpace: 'nowrap',
    opacity: 0,
    pointerEvents: 'none',
    transition: 'opacity 0.2s ease',
    zIndex: 10,
    boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
  },
  showTooltip: {
    opacity: 1,
  },
})

interface PhaseData {
  id: string
  name: string
  status: 'completed' | 'current' | 'pending'
  plannedStart: string
  plannedEnd: string
  actualStart: string
  completion: number
  icon: string
}

const phases: PhaseData[] = [
  {
    id: '1',
    name: 'Foundation',
    status: 'completed',
    plannedStart: 'May 1',
    plannedEnd: 'May 28',
    actualStart: 'May 1',
    completion: 100,
    icon: '✓',
  },
  {
    id: '2',
    name: 'Framing',
    status: 'completed',
    plannedStart: 'May 29',
    plannedEnd: 'Jun 20',
    actualStart: 'May 29',
    completion: 100,
    icon: '✓',
  },
  {
    id: '3',
    name: 'Roofing',
    status: 'current',
    plannedStart: 'Jun 21',
    plannedEnd: 'Jul 10',
    actualStart: 'Jun 21',
    completion: 65,
    icon: '⬤',
  },
  {
    id: '4',
    name: 'Finishing',
    status: 'pending',
    plannedStart: 'Jul 11',
    plannedEnd: 'Aug 05',
    actualStart: '-',
    completion: 0,
    icon: '○',
  },
  {
    id: '5',
    name: 'Inspection',
    status: 'pending',
    plannedStart: 'Aug 06',
    plannedEnd: 'Aug 20',
    actualStart: '-',
    completion: 0,
    icon: '○',
  },
]

export function MasterConstructionTimeline() {
  const styles = useStyles()
  const [hoveredPhase, setHoveredPhase] = useState<string | null>(null)

  return (
    <div className={styles.root}>
      <div className={styles.title}>Master Construction Timeline</div>
      <div className={styles.subtitle}>Project phases and current progress</div>

      <div className={styles.timeline}>
        {phases.map((phase, index) => (
          <div key={phase.id}>
            <div className={styles.phaseContainer} onMouseEnter={() => setHoveredPhase(phase.id)} onMouseLeave={() => setHoveredPhase(null)}>
              <div
                className={`${styles.phaseNode} ${phase.status === 'completed' ? styles.phaseNodeCompleted : ''} ${phase.status === 'current' ? styles.phaseNodeCurrent : ''}`}
              >
                {phase.icon}
                <div className={`${styles.tooltip} ${hoveredPhase === phase.id ? styles.showTooltip : ''}`}>
                  <div style={{ fontWeight: 700 }}>{phase.name}</div>
                  <div>{phase.completion}% Complete</div>
                </div>
              </div>
              <div className={styles.phaseLabel}>
                {phase.name}
                <div className={styles.phaseStatus}>{phase.status === 'current' ? 'Active' : phase.status === 'completed' ? 'Done' : 'Pending'}</div>
              </div>
            </div>
            {index < phases.length - 1 && <div className={`${styles.phaseConnector} ${phase.status === 'completed' ? styles.phaseConnectorCompleted : ''}`} />}
          </div>
        ))}
      </div>
    </div>
  )
}
